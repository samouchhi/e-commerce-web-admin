<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Purchase;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchasePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Purchase');
    }

    public function view(AuthUser $authUser, Purchase $purchase): bool
    {
        return $authUser->can('View:Purchase');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Purchase');
    }

    public function update(AuthUser $authUser, Purchase $purchase): bool
    {
        return $authUser->can('Update:Purchase');
    }

    public function delete(AuthUser $authUser, Purchase $purchase): bool
    {
        return $authUser->can('Delete:Purchase');
    }

}