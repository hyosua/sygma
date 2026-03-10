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
    ];

    protected $casts = [
        'debut_a' => 'datetime',
        'fin_a' => 'datetime',
    ];

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

    /**
     * Vérifie si la séance est actuellement en cours.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $now = now();
        return $now->between($this->debut_a, $this->fin_a);
    }
}
