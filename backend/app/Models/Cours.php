<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cours extends Model
{
    protected $table = 'cours';

    protected $fillable = ['nom'];

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'cours_id');
    }

    public function etudiants()
    {
        return $this->belongsToMany(User::class, 'inscriptions', 'cours_id', 'utilisateur_id');
    }

    public function seances()
    {
        return $this->hasMany(Seance::class, 'cours_id');
    }
}
