<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ControllerCours;
use App\Http\Controllers\EmargementController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ImportCompteController;
use App\Http\Controllers\InvitationGestionnaireController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\SeanceController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GroupeController;

// Publique
Route::post('login', [AuthController::class, 'login']);
Route::get('/groupes/{id}/etudiants', [GroupeController::class, 'etudiants']);
Route::post('register', [AuthController::class, 'register']);
Route::get('email/verify/{token}', [AuthController::class, 'verifierEmail']);
Route::post('auth/google/finaliser', [GoogleAuthController::class, 'finaliser']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::post('/invitations/gestionnaire/{token}', [InvitationGestionnaireController::class, 'inscrire']);
Route::get('/invitations/gestionnaire/{token}', [InvitationGestionnaireController::class, 'verifierToken']);
Route::post('/demandes/gestionnaire', [InvitationGestionnaireController::class, 'demanderAcces']);

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
* Gestionnaires
*
*/
Route::middleware('auth:sanctum', 'role:gestionnaire')->group(function () {
    Route::post('/gestionnaire/invitations', [InvitationGestionnaireController::class, 'inviter']);
    Route::post('/gestionnaire/invitations/{invitation}/renvoyer', [InvitationGestionnaireController::class, 'renvoyer']);
    Route::get('/gestionnaire/invitations', [InvitationGestionnaireController::class, 'getInvitations']);
    Route::delete('/gestionnaire/invitations/{invitation}', [InvitationGestionnaireController::class, 'annuler']);

    Route::get('/gestionnaire/demandes', [InvitationGestionnaireController::class, 'getDemandes']);
    Route::post('/gestionnaire/demandes/{invitation}/approuver', [InvitationGestionnaireController::class, 'approuver']);
    Route::delete('/gestionnaire/demandes/{invitation}', [InvitationGestionnaireController::class, 'refuser']);
    Route::post('/gestionnaire/comptes/import', [ImportCompteController::class, 'importer']);
});

/*
* Etudiant
*
*/
Route::middleware('auth:sanctum', 'role:etudiant|gestionnaire')->group(function () {
    // Émargement
    Route::post('/presences/valider-qr', [EmargementController::class, 'validerPresenceParQR']);

    // Historique des présences
    Route::get('/mes-presences/{user}', [PresenceController::class, 'getPresenceById']);

    // Présence
    // Route::get('/mes-presences/{user}', [PresenceController::class, 'getPresenceById']);
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
});

// Export (réservé aux gestionnaires)
Route::middleware('auth:sanctum', 'role:gestionnaire')->group(function () {
    Route::get('/getExport', [ExportController::class, 'getSessionByDate']);
    Route::get('/getByDay', [ExportController::class, 'getAbsencesToDay']);
    Route::get('/getStatutAndByDate', [ExportController::class, 'getStatutAndByDate']);
});
