<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ControllerCours;
use App\Http\Controllers\EmargementController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\SeanceController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Publique
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

/*
* Enseignants
*
*/
Route::middleware('auth:sanctum', 'role:enseignant|gestionnaire')->group(function () {
    // Emargement
    Route::get('/sessions-emargement/{session}/statut', [EmargementController::class, 'statut']);
    Route::post('/sessions-emargement', [EmargementController::class, 'demarrerSession']);
    Route::post('/sessions-emargement/{session}/refresh', [EmargementController::class, 'rafraichirJeton']);
    Route::post('/sessions-emargement/{session}/cloturer', [EmargementController::class, 'cloturerSession']);
});

/*
* Etudiant
*
*/
Route::middleware('auth:sanctum', 'role:etudiant|gestionnaire')->group(function () {
    // Émargement
    Route::post('/presences/valider-qr', [EmargementController::class, 'validerPresenceParQR']);
});

/*
* Enseignants/Gestionnaires
*
*/
Route::middleware('auth:sanctum', 'role:enseignant|gestionnaire')->group(function () {
    // Séances
    Route::patch('/seances/{seance}', [SeanceController::class, 'modifierSeance']);
    Route::post('/seances', [SeanceController::class, 'creerSeance']);
    Route::delete('/seances/{seance}', [SeanceController::class, 'supprimer']);

    // Cours
    Route::post('/Cours/Ajouter', [ControllerCours::class, 'createCours']);
    Route::patch('/Cours/Modifier/{id}', [ControllerCours::class, 'updateCours']);
    Route::delete('/Cours/Supprimer/{id}', [ControllerCours::class, 'deleteCours']);

    // Émargement
    Route::post('/presences/valider-manuel', [EmargementController::class, 'validerPresenceManuellement']);
});

/*
* Enseignant/Gestionnaire/Etudiant
*
*/
Route::middleware('auth:sanctum')->group(function () {
    // User
    Route::get('/user/{user}', [UserController::class, 'getUser']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('users', [UserController::class, 'addUser']);
    Route::patch('users/{user}', [UserController::class, 'updateUser']);
    Route::delete('users/{user}', [UserController::class, 'deleteUser']);

    // Cours
    Route::get('/cours', [ControllerCours::class, 'getCours']);

    // Groupes
    Route::get('/groupes', function () {
        return \App\Models\Groupe::all(['id', 'nom', 'promotion']);
    });

    // Seance
    Route::get('/seances', [SeanceController::class, 'getSeances']);
    Route::get('/seances/{seance}', [SeanceController::class, 'getSeance']);
    Route::get('/seances/{seance}/sessions-emargement', [SeanceController::class, 'getSessions']);

    // Déconnexion
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
});

// Export
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/getExport', [ExportController::class, 'getSessionByDate']);
    Route::get('/getByDay', [ExportController::class, 'getAbsencesToDay']);
    Route::get('/getStatutAndByDate', [ExportController::class, 'getStatutAndByDate']);
});
