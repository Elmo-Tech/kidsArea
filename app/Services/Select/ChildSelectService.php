<?php

namespace App\Services\Select;

use App\Models\Child;

class ChildSelectService
{
    public function getAllChildren()
    {
        return Child::all(['id as value','name as label']);
    }
}
