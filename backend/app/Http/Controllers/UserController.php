<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{
    function getUser(){
        $User = User::all();
        return $User;
    }

    function addUser(Request $request){
        $nom = $request->input('nom');
        $prenom = $request->input('prenom');
        $email = $request->input("email");
        $password = password_hash($request->input("password") , PASSWORD_BCRYPT);
        $ine = $request->input("ine");
        $specialite =  $request->input("specialites");
        $groupe_id =  $request->input("groupe_id");


        $verify = User::where('email', $email)->get();
        foreach($verify as $v){
            if ($v ->email){
                return  response()->json( "Le compte existe déja", 401);
            }
        }
        $User = User::create([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'password' => $password,
            'ine' => $ine,
            'specialite' => $specialite,
            'groupe_id' => $groupe_id
        ]);

        return  response()->json($User, 200);
    }

     function updateUser(Request $req, $id){


        $User = User::find($id);

        if($User){
            $User->nom = $req->nom;
            $User->prenom = $req->prenom;
            $User->email = $req->email;
            $User->ine = $req->ine;

            $User->Save();
            return response()->json($User,200);
        }
            return response()->json("Utilisateur non trouvé",400);
        

    }

    function deleteUser($id){

        $user = User::find($id);
        if($user){
            $user->delete();
            return response()->json("L'utilisateur à bien été supprimé", 200);

            }
             return response()->json("L'utilisateur n'a pas été trouver", 400);

    }



}