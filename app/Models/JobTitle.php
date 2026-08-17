<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class JobTitle extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'name',
        'description',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
