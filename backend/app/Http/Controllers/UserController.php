<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUser()
    {
        $User = User::all();

        return $User;
    }

    public function addUser(Request $request)
    {
        $nom = $request->input('nom');
        $prenom = $request->input('prenom');
        $email = $request->input('email');
        $password = password_hash($request->input('password'), PASSWORD_BCRYPT);
        $ine = $request->input('ine');
        $specialite = $request->input('specialites');
        $groupe_id = $request->input('groupe_id');

        $verify = User::where('email', $email)->get();
        foreach ($verify as $v) {
            if ($v->email) {
                return response()->json(['message' => 'Le compte existe déjà'], 409);
            }
        }
        $User = User::create([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'password' => $password,
            'ine' => $ine,
            'specialite' => $specialite,
            'groupe_id' => $groupe_id,
        ]);

        return response()->json($User, 201);
    }

    public function updateUser(Request $req, $id)
    {

        $User = User::find($id);

        if ($User) {
            $User->nom = $req->nom;
            $User->prenom = $req->prenom;
            $User->email = $req->email;
            $User->ine = $req->ine;

            $User->Save();

            return response()->json($User, 200);
        }

        return response()->json(['message' => 'Utilisateur non trouvé'], 404);
    }

    public function deleteUser($id)
    {

        $user = User::find($id);
        if ($user) {
            $user->delete();

            return response()->json("L'utilisateur à bien été supprimé", 200);
        }

        return response()->json(['message' => "L'utilisateur n'a pas été trouvé"], 404);
    }
}
