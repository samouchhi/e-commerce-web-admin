<?php

namespace App\Models;

use Asignua\FilamentActivityLogPlus\Concerns\LogsActivityPlus;
use Spatie\Permission\Models\Role as PermissionRole;

class Role extends PermissionRole
{
    use LogsActivityPlus;
}
