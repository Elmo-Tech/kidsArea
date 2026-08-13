<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CreatedUpdatedBy;

class ParameterValue extends Model
{
    use CreatedUpdatedBy;

    protected $fillable = [
        'name',
        'description',
        'parameter_id',
    ];

    public function parameter()
    {
        return $this->belongsTo(Parameter::class);
    }
}
