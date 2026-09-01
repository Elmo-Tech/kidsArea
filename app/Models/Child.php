<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Child extends Model
{
    protected $fillable = [
        'name',
        'birth_date',
        'gender',
        'notes',

        'guardian_name',
        'guardian_phone',
        'guardian_relation',
        'guardian_email',
        'guardian_notes',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function activitySessions(): BelongsToMany
    {
        return $this->belongsToMany(
            ActivitySession::class,
            'activity_session_children'
        );
    }

    public function activityMemberships(): HasMany
    {
        return $this->hasMany(
            ActivityMembership::class
        );
    }

    public function activityAttendances(): HasMany
    {
        return $this->hasMany(
            ActivityAttendance::class
        );
    }
}
