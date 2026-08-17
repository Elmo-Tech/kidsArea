<?php
namespace App\Services\Select;

use App\Models\ParameterValue;

class ParameterSelectService
{

    public function getAllParameters(int $type)
    {
        return ParameterValue::where('parameter_order', $type)->get(['id as value', 'name as label']);
    }
}
