<?php

namespace App\Http\Controllers;

use App\Services\EmargementService;
use App\Models\Seance;
use App\Models\SessionEmargement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Exceptions\JetonInvalideException;
use App\Exceptions\JetonExpireException;
use App\Exceptions\SeanceNonActiveException;
use App\Exceptions\DejaEmargeException;
use App\Exceptions\EtudiantNonInscritException;
use Exception;

class EmargementController extends Controller
{
    protected $emargementService;

    public function __construct(EmargementService $emargementService)
    {
        $this->emargementService = $emargementService;
    }

    /**
     * Démarre une session d'émargement pour une séance.
     */
    public function demarrerSession(Request $request)
    {
        $request->validate([
            'seance_id' => 'required|exists:seances,id',
            'is_methode_qr' => 'required|boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $seance = Seance::findOrFail($request->seance_id);

        // Vérifier si l'utilisateur est l'enseignant de la séance
        // if ($seance->enseignant_id !== Auth::id()) {
        //     return response()->json(['message' => 'Non autorisé'], 403);
        // }

        $coordonnees = [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ];

        try {
            $session = $this->emargementService->demarrerSession($seance, (bool) $request->is_methode_qr, $coordonnees);
            return response()->json($session, 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Rafraîchit le jeton d'une session d'émargement.
     */
    public function rafraichirJeton(SessionEmargement $session)
    {
        try {
            $sessionUpdated = $this->emargementService->rafraichirJeton($session);
            return response()->json($sessionUpdated);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Valide la présence d'un étudiant via un jeton.
     */
    public function validerPresenceParQR(Request $request)
    {
        $request->validate([
            'jeton' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $etudiant = Auth::user();
        
        // Temporaire : Si pas d'utilisateur authentifié (test), on prend le premier utilisateur
        if (!$etudiant) {
            $etudiant = \App\Models\User::first();
        }

        if (!$etudiant) {
             return response()->json(['message' => 'Aucun utilisateur trouvé en base pour le test'], 404);
        }

        $coordonnees = [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ];

        try {
            $presence = $this->emargementService->validerPresenceParJeton($request->jeton, $etudiant, $coordonnees);
            $statusCode = 200;
            $response = [
                'message' => 'Présence validée avec succès',
                'presence' => $presence
            ];
        } catch (JetonInvalideException $e) {
            $statusCode = 400;
            $response = ['message' => 'Jeton invalide'];
        } catch (JetonExpireException $e) {
            $statusCode = 400;
            $response = ['message' => 'Le QR Code a expiré, veuillez scanner le nouveau'];
        } catch (SeanceNonActiveException $e) {
            $statusCode = 400;
            $response = ['message' => 'La séance n\'est pas active'];
        } catch (DejaEmargeException $e) {
            $statusCode = 400;
            $response = ['message' => 'Vous avez déjà émargé pour cette séance'];
        } catch (Exception $e) {
            $statusCode = 500;
            $response = ['message' => 'Une erreur est survenue lors de la validation'];
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Valide la présence d'un étudiant manuellement.
     */
    public function validerPresenceManuellement(Request $request)
    {
        $data = $request->validate([
            'session_emargement_id' => 'required|exists:sessions_emargement,id',
            'etudiant_id' => 'required|exists:users,id',
        ]);

        try {
            $session = SessionEmargement::findOrFail($data['session_emargement_id']);
            $etudiant = User::findOrFail($data['etudiant_id']);

            $presence = $this->emargementService->enregistrerPresence($session, $etudiant);

            return response()->json([
                'message' => 'Présence validée avec succès',
                'presence' => $presence
            ]);
        }catch (DejaEmargeException){
            return response()->json([
                'message' => 'L\'étudiant a déjà émargé pour cette session'
            ], 400);
        }catch (EtudiantNonInscritException){
            return response()->json([
                'message' => 'L\'étudiant n\'est pas inscrit à cette séance'
            ], 400);
        }catch (Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la validation'
            ], 500);
        }
    }

    /**
     * Clôture une session d'émargement.
     */
    public function cloturerSession(SessionEmargement $session)
    {
        try {
            $sessionUpdated = $this->emargementService->cloturerSession($session);
            return response()->json($sessionUpdated);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Récupère le statut d'une session (nombre de présents, etc.)
     * Rafraîchit automatiquement le jeton si celui-ci est expiré.
     */
    public function status(SessionEmargement $session)
    {
        // Si le jeton est expiré, on le rafraîchit automatiquement
        if ($session->is_methode_qr && $session->expire_a && $session->expire_a->isPast()) {
            $this->emargementService->rafraichirJeton($session);
        }

        $session->load('presences');
        return response()->json([
            'id' => $session->id,
            'jeton' => $session->jeton,
            'expire_a' => $session->expire_a,
            'nombre_presents' => $session->presences()->count(),
        ]);
    }
}
