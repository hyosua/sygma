<?php

namespace App\Http\Controllers;

use App\Exceptions\NonAutoriseException;
use App\Models\Presence;
use App\Models\Seance;
use App\Models\SessionEmargement;
use App\Models\User;
use App\Services\EmargementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmargementController extends Controller
{
    public function __construct(private EmargementService $emargementService)
    {
    }

    // Démarre une session d'émargement pour une séance.
    public function demarrerSession(Request $request)
    {
        $request->validate([
            'seance_id' => 'required|exists:seances,id',
            'is_methode_qr' => 'required|boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $seance = Seance::findOrFail($request->seance_id);

        if ($seance->enseignant_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $coordonnees = [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ];

        $session = $this->emargementService->demarrerSession($seance, (bool) $request->is_methode_qr, $coordonnees);

        return response()->json($session, 201);
    }

    // Rafraîchit le jeton d'une session d'émargement.

    public function rafraichirJeton(SessionEmargement $session)
    {
        if ($session->seance->enseignant_id !== Auth::id()) {
            throw new NonAutoriseException();
        }

        $sessionUpdated = $this->emargementService->rafraichirJeton($session);

        return response()->json($sessionUpdated);
    }

    // Valide la présence d'un étudiant via un jeton.

    public function validerPresenceParQR(Request $request)
    {
        $request->validate([
            'jeton' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $etudiant = Auth::user();

        if (! $etudiant) {
            return response()->json(['message' => 'Aucun utilisateur trouvé en base pour le test'], 404);
        }

        $coordonnees = [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ];

        $presence = $this->emargementService->validerPresenceParJeton($request->jeton, $etudiant, $coordonnees);

        return response()->json([
            'message' => 'Présence validée avec succès',
            'presence' => $presence,
        ]);
    }

    // Valide la présence d'un étudiant manuellement.
    public function validerPresenceManuellement(Request $request)
    {
        $data = $request->validate([
            'session_emargement_id' => 'required|exists:sessions_emargement,id',
            'etudiant_id' => 'required|exists:users,id',
        ]);

        $session = SessionEmargement::findOrFail($data['session_emargement_id']);

        // La validation manuelle est également ouverte aux gestionnaires (ex: après avoir recu un justificatif)
        if ($session->seance->enseignant_id !== Auth::id() && ! Auth::user()->hasRole('gestionnaire')) {
            throw new NonAutoriseException();
        }

        $etudiant = User::findOrFail($data['etudiant_id']);

        $presence = Presence::updateOrCreate(
            ['session_emargement_id' => $session->id, 'etudiant_id' => $etudiant->id],
            ['statut' => 'present', 'scanne_a' => now()]
        );

        return response()->json([
            'message' => 'Présence validée avec succès',
            'presence' => $presence,
        ]);
    }

    // Clôture une session d'émargement.
    public function cloturerSession(SessionEmargement $session)
    {
        if ($session->seance->enseignant_id !== Auth::id()) {
            throw new NonAutoriseException();
        }

        $sessionUpdated = $this->emargementService->cloturerSession($session);

        return response()->json($sessionUpdated);
    }

    // Récupère le statut d'une session (nombre de présents, etc.)
    public function statut(SessionEmargement $session)
    {
        return response()->json($this->emargementService->obtenirStatut($session));
    }
}
