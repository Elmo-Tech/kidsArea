<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityUsagePause extends Model
{
    protected $fillable = [
        'activity_usage_id',
        'paused_at',
        'resumed_at',
        'duration_minutes',
    ];

    protected function casts(): array
    {
        return [
            'paused_at' =>
                'datetime',

            'resumed_at' =>
                'datetime',
        ];
    }

    public function usage(): BelongsTo
    {
        return $this->belongsTo(
            ActivityUsage::class,
            'activity_usage_id'
        );
    }
}
