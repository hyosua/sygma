<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionEmargement extends Model
{
    protected $table = 'sessions_emargement';

    protected $fillable = [
        'seance_id',
        'methode',
        'jeton',
        'expire_a',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'expire_a' => 'datetime',
    ];

    public function seance()
    {
        return $this->belongsTo(Seance::class, 'seance_id');
    }

    public function presences()
    {
        return $this->hasMany(Presence::class, 'session_emargement_id');
    }
}
