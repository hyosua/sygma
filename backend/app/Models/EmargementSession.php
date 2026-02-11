<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmargementSession extends Model
{
    protected $fillable = [
        'course_session_id',
        'method',
        'token',
        'expires_at',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function courseSession()
    {
        return $this->belongsTo(CourseSession::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
