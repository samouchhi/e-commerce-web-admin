<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Discount;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class DiscountPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Discount');
    }

    public function view(AuthUser $authUser, Discount $discount): bool
    {
        return $authUser->can('View:Discount');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Discount');
    }

    public function update(AuthUser $authUser, Discount $discount): bool
    {
        return $authUser->can('Update:Discount');
    }

    public function delete(AuthUser $authUser, Discount $discount): bool
    {
        return $authUser->can('Delete:Discount');
    }
}
