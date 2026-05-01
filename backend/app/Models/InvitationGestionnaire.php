<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvitationGestionnaire extends Model
{
    use HasFactory;

    protected $table = 'invitations_gestionnaire';

    protected $fillable = [
        'email',
        'token',
        'expires_at',
        'used_at',
        'demande',
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
        'demande' => 'boolean',
    ];

    public function estValide()
    {
        return $this->expires_at?->isFuture() && ! $this->used_at;
    }
}
