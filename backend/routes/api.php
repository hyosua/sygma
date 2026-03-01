<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ControllerCours;

use App\Http\Controllers\EmargementController;

Route::get('/user/{id}', [UserController::class, 'show']);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes pour l'émargement (Auth temporairement retirée pour tests)
Route::post('/sessions-emargement', [EmargementController::class, 'demarrerSession']);
Route::post('/sessions-emargement/{session}/refresh', [EmargementController::class, 'rafraichirJeton']);
Route::post('/sessions-emargement/{session}/cloturer', [EmargementController::class, 'cloturerSession']);
Route::get('/sessions-emargement/{session}/status', [EmargementController::class, 'status']);
Route::post('/presences/valider', [EmargementController::class, 'validerPresence']);

Route::middleware('auth:sanctum')->group(function () {
    // Autres routes protégées si nécessaire
});

Route::get('/getUsers',[ControllerCours::class ,'getUsers']);