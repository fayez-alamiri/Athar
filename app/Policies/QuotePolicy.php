<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;

class QuotePolicy
{
    /**
     * Guests and authenticated users can browse quotes.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Guests and authenticated users can view individual quotes.
     */
    public function view(?User $user, Quote $quote): bool
    {
        return true;
    }

    /**
     * Any authenticated user may publish quick quotes and book quotes.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the platform publisher (owner) of a post may edit it.
     */
    public function update(User $user, Quote $quote): bool
    {
        return (int) $user->id === (int) $quote->user_id;
    }

    /**
     * Only the platform publisher (owner) of a post may delete it.
     */
    public function delete(User $user, Quote $quote): bool
    {
        return (int) $user->id === (int) $quote->user_id;
    }
}