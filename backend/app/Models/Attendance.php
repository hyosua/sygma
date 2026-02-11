<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'emargement_session_id',
        'student_id',
        'status',
        'scanned_at',
        'notes',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function emargementSession()
    {
        return $this->belongsTo(EmargementSession::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
