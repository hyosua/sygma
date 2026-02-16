<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Groupe extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'promotion',
    ];

    /**
     * Get the users (students) for the group.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the seances for the group.
     */
    public function seances(): HasMany
    {
        return $this->hasMany(Seance::class);
    }
}
