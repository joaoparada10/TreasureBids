<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Auction;
use App\Models\Member;
use Illuminate\Auth\Access\Response;

class AuctionPolicy
{
    /**
     * Determine whether the user (Admin or Member) can view any auctions.
     */
    public function viewAny($user): bool
    {
        return $user instanceof Admin || $user instanceof Member;
    }

    /**
     * Determine whether the user (Admin or Member) can view the auction.
     */
    public function view($user, Auction $auction): bool
    {
        return $user instanceof Admin || ($user instanceof Member && $user->id === $auction->owner_id);
    }

    /**
     * Determine whether the user (Admin) can create auctions.
     */
    public function create($user): bool
    {
        return $user instanceof Admin || $user instanceof Member;
    }

    /**
     * Determine whether the user (Admin or Member) can update the auction.
     */
    public function update($user, Auction $auction): bool
    {
        return $user instanceof Admin || ($user instanceof Member && $user->id === $auction->owner_id);
    }

    /**
     * Determine whether the user (Admin) can delete the auction.
     */
    public function cancel($user, Auction $auction): bool
    {
        return $user instanceof Admin || ($user instanceof Member && $user->id === $auction->owner_id);
    }

    /**
     * Determine whether the user (Admin) can restore the auction.
     */
    public function restore($user, Auction $auction): bool
    {
        return $user instanceof Admin;
    }

    /**
     * Determine whether the user (Admin) can permanently delete the auction.
     */
    public function forceDelete($user, Auction $auction): bool
    {
        return $user instanceof Admin;
    }


    public function delete($user, Auction $auction): bool
    {
        return $user instanceof Admin;
    }

    

}
