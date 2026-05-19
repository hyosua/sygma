<?php

namespace App\Http\Controllers;

use App\Models\User;

class GroupeController extends Controller
{
public function etudiants($id)
{
    return response()->json(
        User::where('groupe_id', $id)->get()
    );
}
}