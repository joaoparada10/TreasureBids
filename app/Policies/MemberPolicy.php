<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Member;
use Illuminate\Auth\Access\Response;

class MemberPolicy
{
    /**
     * Determine whether the user (Admin or Member) can view any models.
     */
    public function viewAny($user): bool
    {
        return true;
    }

    /**
     * Determine whether the user (Admin or Member) can view the model.
     */
    public function view($user, Member $member): bool
    {
        return true;
    }

    /**
     * Determine whether the user (Admin) can create models.
     */
    public function create($user): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Determine whether the user (Admin or Member) can update the model.
     */
    public function update($user, Member $member): bool
    {
        
        if ($user instanceof Admin) {
            return true;
        }
        return $user instanceof Member && $user->id === $member->id;
    }

    /**
     * Determine whether the user (Admin) can delete the model.
     */
    public function delete($user, Member $member): bool
    {
        if ($user instanceof Admin) {
            return true;
        }
        return $user instanceof Member && $user->id === $member->id;
    }

    /**
     * Determine whether the user (Admin) can restore the model.
     */
    public function restore($user, Member $member): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Determine whether the user (Admin) can permanently delete the model.
     */
    public function forceDelete($user, Member $member): bool
    {
        return $user instanceof Admin;
    }
}
