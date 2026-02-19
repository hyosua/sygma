<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


class ControllerCours extends Controller
{
    function getUsers(){
        $cours = User::all();
        return $cours;
    }

}