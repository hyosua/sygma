<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'email' => 'required|email',
            'password' => 'required|min:8',
            'role' => 'required|in:etudiant,enseignant',
        ]);

        if (User::where('email', $req->email)->exists()) {
            return response()->json(['message' => 'Un compte existe déjà avec cet email.'], 409);
        }

        if (! in_array($req->role, ['etudiant', 'enseignant'])) {
            return response()->json(['message' => 'Rôle invalide.'], 400);
        }

        $user = User::create([
            'nom' => $req->nom,
            'prenom' => $req->prenom,
            'email' => $req->email,
            'password' => Hash::make($req->password),
        ]);

        $user->assignRole($req->role);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function logout(Request $req)
    {
        $req->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès'], 200);
    }
}
