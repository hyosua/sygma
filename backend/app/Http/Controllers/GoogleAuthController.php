<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function rediriger()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback()
    {
        $utilisateurGoogle = Socialite::driver('google')->stateless()->user();

        // Cas 1 : google_id connu
        $user = User::where('google_id', $utilisateurGoogle->getId())->first();
        if ($user) {
            $token = $user->createToken('api-token')->plainTextToken;

            return redirect(config('app.frontend_url') . '/auth/google/succes?token=' . $token);
        }

        // Cas 2 : email connu, google_id null
        $user = User::where('email', $utilisateurGoogle->getEmail())->first();
        if ($user) {
            $user->google_id = $utilisateurGoogle->getId();
            $user->save();

            $token = $user->createToken('api-token')->plainTextToken;

            return redirect(config('app.frontend_url') . '/auth/google/succes?token=' . $token);
        }

        // Cas 3 : nouvel utilisateur — token temporaire (TTL 5 min)
        $tokenTemporaire = Str::uuid()->toString();
        Cache::put('google_temp_' . $tokenTemporaire, [
            'google_id' => $utilisateurGoogle->getId(),
            'email' => $utilisateurGoogle->getEmail(),
            'nom' => $utilisateurGoogle->user['family_name'] ?? $utilisateurGoogle->getName(),
            'prenom' => $utilisateurGoogle->user['given_name'] ?? '',
        ], now()->addMinutes(5));

        return redirect(
            config('app.frontend_url') . '/inscription/choisir-role?token=' . $tokenTemporaire
        );
    }

    public function finaliser(Request $req)
    {
        $req->validate([
            'token_temporaire' => 'required|string',
            'role' => 'required|in:etudiant,enseignant',
        ]);

        $donnees = Cache::get('google_temp_' . $req->token_temporaire);

        if (! $donnees) {
            return response()->json(['message' => 'Token temporaire invalide ou expiré.'], 422);
        }

        $user = User::create([
            'nom' => $donnees['nom'],
            'prenom' => $donnees['prenom'],
            'email' => $donnees['email'],
            'google_id' => $donnees['google_id'],
            'password' => null,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($req->role);

        Cache::forget('google_temp_' . $req->token_temporaire);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }
}
