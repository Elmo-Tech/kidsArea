<?php

declare(strict_types=1);

namespace App\Exceptions\Activity;

use Exception;

class ChildNotAssignedToActivitySessionException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            __('activity.child_not_assigned_to_activity_session')
        );
    }
}
