<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    use HasFactory;

    protected $fillable = [
        'cours_id',
        'enseignant_id',
        'groupe_id',
        'debut_a',
        'fin_a',
        'salle',
    ];

    // permet de convertir les champs date de la table en instances de Carbon
    protected $casts = [
        'debut_a' => 'datetime',
        'fin_a' => 'datetime',
        'salle' => 'integer',
    ];

    // ajoute un attribut virtuel 'statut-seance' qui indique si la séance est en cours, à venir ou terminée
    protected $appends = ['statut'];

    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }

    public function cours()
    {
        return $this->belongsTo(Cours::class, 'cours_id');
    }

    public function enseignant()
    {
        return $this->belongsTo(User::class, 'enseignant_id');
    }

    public function sessionsEmargement()
    {
        return $this->hasMany(SessionEmargement::class, 'seance_id');
    }

    public function etudiants()
    {
        return $this->hasMany(User::class, 'groupe_id', 'groupe_id');
    }

    // Vérifie si la séance est actuellement en cours.

    public function isActive(): bool
    {
        return $this->getStatut() === 'en_cours';
    }

    // Obtenir le statut de la séance
    public function getStatut(): string
    {
        $now = now();

        if ($now->lt($this->debut_a)) {
            return 'a_venir';
        } elseif ($now->between($this->debut_a, $this->fin_a)) {
            return 'en_cours';
        }

        return 'terminee';
    }

    public function getStatutAttribute()
    {
        return $this->getStatut();
    }

    // Connaître le statut d'une salle
    public static function salleEstOccupee(int $salle): bool
    {
        $now = now();

        return self::where('salle', $salle)->where('debut_a', '<=', $now)->where('fin_a', '>=', $now)->exists();
    }
}
