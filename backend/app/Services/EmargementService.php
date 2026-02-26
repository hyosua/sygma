<?php

namespace App\Services;

use App\Models\SessionEmargement;
use App\Models\Seance;
use App\Models\User;
use App\Models\Presence;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class EmargementService
{
    protected const DUREE_VALIDITE_JETON = 20;

    /**
     * Initialise une nouvelle session d'émargement pour une séance donnée.
     *
     * @param Seance $seance
     * @param string $methode 'qr' ou 'manual'
     * @param array $coordonnees ['latitude' => x, 'longitude' => y] optionnel
     * @return SessionEmargement
     */
    public function demarrerSession(Seance $seance, string $methode = 'qr', array $coordonnees = []): SessionEmargement
    {
        return SessionEmargement::create([
            'seance_id' => $seance->id,
            'methode' => $methode,
            'jeton' => ($methode === 'qr') ? $this->genererJeton() : null,
            'expire_a' => ($methode === 'qr') ? Carbon::now()->addSeconds(self::DUREE_VALIDITE_JETON) : null,
            'latitude' => $coordonnees['latitude'] ?? null,
            'longitude' => $coordonnees['longitude'] ?? null,
        ]);
    }

    /**
     * Valide un jeton QR Code et enregistre la présence de l'étudiant.
     *
     * @param string $jeton
     * @param User $etudiant
     * @param array $coordonneesEtudiant ['latitude' => x, 'longitude' => y] optionnel
     * @return Presence
     * @throws Exception
     */
    public function validerPresenceParJeton(string $jeton, User $etudiant, array $coordonneesEtudiant = []): Presence
    {
        // On récupère la session d'émargement associée au jeton
        $session = SessionEmargement::where('jeton', $jeton)->first();

        if (!$session) {
            throw new Exception("Jeton d'émargement invalide.");
        }

        if ($session->expire_a && $session->expire_a->isPast()) {
            throw new Exception("QR Code expiré, veuillez scanner le nouveau.");
        }

        // Vérification que la session d'émargement est bien associée à une séance active
        $seance = $session->seance;
        if (!$seance || !$seance->isActive()) {
            throw new Exception("La séance associée à ce jeton n'est pas active.");
        }

        // Vérification si l'étudiant a déjà émargé pour cette session
        $dejaPresent = Presence::where('session_emargement_id', $session->id)
            ->where('etudiant_id', $etudiant->id)
            ->exists();

        if ($dejaPresent) {
            throw new Exception("Vous avez déjà émargé pour cette séance.");
        }

        // à faire plus tard : Logique de vérification de distance si $session->latitude est défini

        return Presence::create([
            'session_emargement_id' => $session->id,
            'etudiant_id' => $etudiant->id,
            'statut' => 'present',
            'scanne_a' => Carbon::now(),
            'latitude' => $coordonneesEtudiant['latitude'] ?? null,
            'longitude' => $coordonneesEtudiant['longitude'] ?? null,
        ]);
    }

    /**
     * Génère un nouveau jeton pour une session active (Rotation du jeton).
     *
     * @param SessionEmargement $session
     * @return SessionEmargement
     */
    public function rafraichirJeton(SessionEmargement $session): SessionEmargement
    {
        $session->update([
            'jeton' => $this->genererJeton(),
            'expire_a' => Carbon::now()->addSeconds(self::DUREE_VALIDITE_JETON),
        ]);

        return $session;
    }

    /**
     * Génère une chaîne aléatoire unique pour le jeton.
     *
     * @return string
     */
    protected function genererJeton(): string
    {
        return Str::random(32);
    }

    /* Prochaines méthodes à implémenter: 
    * -fonction de calcul de distance entre les coordonnées de la session et celles de l'étudiant
    * -fonction qui vérifie qu'un étudiant est bien inscrit à la séance
    * - fonction pour cloturer une session d'émargement
    * -...
    */
}
