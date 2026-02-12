<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    protected $fillable = [
        'cours_id',
        'enseignant_id',
        'debut_a',
        'fin_a',
    ];

    protected $casts = [
        'debut_a' => 'datetime',
        'fin_a' => 'datetime',
    ];

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
}
