<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionEmargement extends Model
{
    use HasFactory;

    protected $table = 'sessions_emargement';

    protected $fillable = [
        'seance_id',
        'is_methode_qr',
        'jeton',
        'expire_a',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'expire_a' => 'datetime',
        'is_methode_qr' => 'boolean',
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
