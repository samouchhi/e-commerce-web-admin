<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Logistic;
use Illuminate\Auth\Access\HandlesAuthorization;

class LogisticPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Logistic');
    }

    public function view(AuthUser $authUser, Logistic $logistic): bool
    {
        return $authUser->can('View:Logistic');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Logistic');
    }

    public function update(AuthUser $authUser, Logistic $logistic): bool
    {
        return $authUser->can('Update:Logistic');
    }

    public function delete(AuthUser $authUser, Logistic $logistic): bool
    {
        return $authUser->can('Delete:Logistic');
    }

}