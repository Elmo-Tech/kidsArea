<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ActivityAttendanceStatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityAttendance extends Model
{
    use CreatedUpdatedBy;
    protected $fillable = [
        'activity_session_id',
        'child_id',
        'activity_membership_id',
        'check_in_at',
        'check_out_at',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' =>
                ActivityAttendanceStatusEnum::class,
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(
            ActivitySession::class,
            'activity_session_id'
        );
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(
            Child::class
        );
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(
            ActivityMembership::class,
            'activity_membership_id'
        );
    }
}
