<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Mail\ConfirmationEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $req)
    {
        $req->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $req->email)->first();

        if (! $user || ! Hash::check($req->password, $user->password)) {
            return response()->json(['message' => 'Email ou mot de passe incorrect'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function register(Request $req)
    {
        $req->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:etudiant,enseignant',
        ]);

        $tokenVerification = Str::uuid()->toString();

        $user = User::create([
            'nom' => $req->nom,
            'prenom' => $req->prenom,
            'email' => $req->email,
            'password' => Hash::make($req->password),
            'verification_token' => $tokenVerification,
            'verification_token_expires_at' => now()->addHours(24),
        ]);

        $user->assignRole($req->role);

        $lienVerification = config('app.frontend_url') . '/email/verify/' . $tokenVerification;
        Mail::to($user->email)->send(new ConfirmationEmail($lienVerification));

        return response()->json([
            'message' => 'Compte créé. Vérifiez votre email pour activer votre compte.',
        ], 201);
    }

    public function verifierEmail(string $token)
    {
        $user = User::where('verification_token', $token)->first();

        if (! $user) {
            return response()->json(['message' => 'Token de vérification invalide.'], 404);
        }

        if (now()->isAfter($user->verification_token_expires_at)) {
            return response()->json(['message' => 'Le lien de vérification a expiré.'], 410);
        }

        $user->email_verified_at = now();
        $user->verification_token = null;
        $user->verification_token_expires_at = null;
        $user->save();

        $sanctumToken = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user),
            'token' => $sanctumToken,
        ]);
    }

    public function logout(Request $req)
    {
        $req->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès'], 200);
    }
}
