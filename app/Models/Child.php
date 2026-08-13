<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Child extends Model
{
    protected $fillable = [
        'name',
        'birth_date',
        'phone',
        'notes',
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
}
