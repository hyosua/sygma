<?php

namespace App\Services;

use App\Exceptions\Emargement\DejaEmargeException;
use App\Exceptions\Emargement\EtudiantNonInscritException;
use App\Exceptions\Emargement\JetonExpireException;
use App\Exceptions\Emargement\JetonInvalideException;
use App\Exceptions\Seance\SeanceNonActiveException;
use App\Models\Presence;
use App\Models\Seance;
use App\Models\SessionEmargement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EmargementService
{
    protected const DUREE_VALIDITE_JETON = 20;

    /**
     * Initialise une nouvelle session d'émargement pour une séance donnée.
     *
     * @param  string  $methode  'qr' ou 'manual'
     * @param  array  $coordonnees  ['latitude' => x, 'longitude' => y] optionnel
     */
    public function demarrerSession(Seance $seance, bool $isMethodeQr = true, array $coordonnees = []): SessionEmargement
    {
        if (! $seance->isActive()) {
            throw new SeanceNonActiveException();
        }

        return SessionEmargement::create([
            'seance_id' => $seance->id,
            'is_methode_qr' => $isMethodeQr,
            'jeton' => $isMethodeQr ? $this->genererJeton() : null,
            'jeton_expire_a' => $isMethodeQr ? Carbon::now()->addSeconds(self::DUREE_VALIDITE_JETON) : null,
            'latitude' => $coordonnees['latitude'] ?? null,
            'longitude' => $coordonnees['longitude'] ?? null,
        ]);
    }

    /**
     * Valide un jeton QR Code et enregistre la présence de l'étudiant.
     *
     * @param  array  $coordonneesEtudiant  ['latitude' => x, 'longitude' => y] optionnel
     *
     * @throws Exception
     */
    public function validerPresenceParJeton(string $jeton, User $etudiant, array $coordonneesEtudiant = []): Presence
    {
        // On récupère la session d'émargement associée au jeton
        $session = SessionEmargement::where('jeton', $jeton)->first();

        if (! $session) {
            throw new JetonInvalideException();
        }

        if ($session->jeton_expire_a && $session->jeton_expire_a->isPast()) {
            throw new JetonExpireException();
        }

        $seance = $session->seance;
        if (! $seance->isActive()) {
            throw new SeanceNonActiveException();
        }

        // à faire plus tard : Logique de vérification de distance si $session->latitude est défini

        return $this->enregistrerPresence($session, $etudiant, $coordonneesEtudiant);
    }

    /**
     * Enregistrer la présence
     */
    public function enregistrerPresence(SessionEmargement $session, User $etudiant, array $coordonneesEtudiant = []): Presence
    {

        // Vérification si l'étudiant a déjà émargé pour cette session
        $dejaPresent = Presence::where('session_emargement_id', $session->id)
            ->where('etudiant_id', $etudiant->id)
            ->exists();

        if ($dejaPresent) {
            throw new DejaEmargeException();
        }

        $etudiantInscrit = $session->seance->etudiants()->where('users.id', $etudiant->id)->exists();

        if (! $etudiantInscrit) {
            throw new EtudiantNonInscritException();
        }

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
     */
    public function rafraichirJeton(SessionEmargement $session): SessionEmargement
    {
        $session->update([
            'jeton' => $this->genererJeton(),
            'jeton_expire_a' => Carbon::now()->addSeconds(self::DUREE_VALIDITE_JETON),
        ]);

        return $session;
    }

    /**
     * Clôture une session d'émargement en mettant à jour sa date d'expiration à maintenant.
     */
    public function cloturerSession(SessionEmargement $session): SessionEmargement
    {
        $session->update([
            'cloture_a' => Carbon::now(),
        ]);

        return $session;
    }

    /**
     * Retourne le statut d'une session : liste des étudiants et leur présence.
     * Rafraîchit automatiquement le jeton s'il est expiré et la session non clôturée.
     */
    public function obtenirStatut(SessionEmargement $session): array
    {
        if ($session->is_methode_qr && is_null($session->cloture_a) && $session->jeton_expire_a && $session->jeton_expire_a->isPast()) {
            $this->rafraichirJeton($session);
        }

        $session->load(['seance.groupe.users', 'presences']);
        $etudiants = $session->seance->groupe->users;
        $presences = $session->presences->keyBy('etudiant_id');

        $listeEtudiants = $etudiants->map(fn ($etudiant) => [
            'etudiant_id' => $etudiant->id,
            'nom' => $etudiant->nom,
            'prenom' => $etudiant->prenom,
            'statut' => $presences->get($etudiant->id)?->statut,
            'scanne_a' => $presences->get($etudiant->id)?->scanne_a,
        ]);

        return [
            'id' => $session->id,
            'jeton' => $session->jeton,
            'jeton_expire_a' => $session->jeton_expire_a,
            'cloture_a' => $session->cloture_a,
            'nombre_presents' => $presences->count(),
            'liste_etudiants' => $listeEtudiants,
        ];
    }

    /**
     * Génère une chaîne aléatoire unique pour le jeton.
     */
    protected function genererJeton(): string
    {
        return Str::random(32);
    }

    /* Prochaines méthodes à implémenter:
    * -fonction de calcul de distance entre les coordonnées de la session et celles de l'étudiant
    * -fonction qui vérifie qu'un étudiant est bien inscrit à la séance
    * -...
    */
}
