<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ControllerCours;
use App\Http\Controllers\EmargementController;
use App\Http\Controllers\SeanceController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user/{id}', [UserController::class, 'getUser']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('users/AddUser', [UserController::class, 'addUser']);
Route::patch('users/UpdateUser/{id}', [UserController::class, 'updateUser']);
Route::delete('users/DeleteUser/{id}', [UserController::class, 'deleteUser']);

// Emargement (Auth temporairement retirée pour tests)
Route::post('/sessions-emargement', [EmargementController::class, 'demarrerSession']);
Route::post('/sessions-emargement/{session}/refresh', [EmargementController::class, 'rafraichirJeton']);
Route::post('/sessions-emargement/{session}/cloturer', [EmargementController::class, 'cloturerSession']);
Route::get('/sessions-emargement/{session}/status', [EmargementController::class, 'status']);
Route::post('/presences/valider', [EmargementController::class, 'validerPresence']);

// Séances
Route::get('/seances', [SeanceController::class, 'getSeances']);
Route::get('/seances/{seance}', [SeanceController::class, 'getSeance']);

Route::middleware('auth:sanctum')->group(function () {
    // Autres routes protégées si nécessaire
});

// Cours
Route::get('/Cours', [ControllerCours::class, 'getCours']);
Route::post('/Cours/Ajouter', [ControllerCours::class, 'createCours']);
Route::patch('/Cours/Modifier/{id}', [ControllerCours::class, 'updateCours']);
Route::delete('/Cours/Supprimer/{id}', [ControllerCours::class, 'deleteCours']);

// Connexion
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
