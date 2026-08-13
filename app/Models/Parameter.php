<?php

namespace App\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class Parameter extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'name',
        'order',
    ];
}
