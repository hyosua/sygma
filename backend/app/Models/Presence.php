<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    protected $fillable = [
        'session_emargement_id',
        'etudiant_id',
        'statut',
        'scanne_a',
        'notes',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'scanne_a' => 'datetime',
    ];

    public function sessionEmargement()
    {
        return $this->belongsTo(SessionEmargement::class, 'session_emargement_id');
    }

    public function etudiant()
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }
}
