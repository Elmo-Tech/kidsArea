<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\StatusEnum;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    protected $fillable = [
        'username',
        'password',
        'status',
        'avatar',
        'employee_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => StatusEnum::class,
        ];
    }

    public function isActive(): bool
    {
        return $this->status === StatusEnum::ACTIVE;
    }

    public function avatar()
    {
        return Attribute::make(
            get: fn (?string $value) => $value
                ? Storage::disk('public')->url($value)
                : null,
        );
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

}
