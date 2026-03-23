<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    public function view(User $user, SupportTicket $supportTicket): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    public function update(User $user, SupportTicket $supportTicket): bool
    {
        return $user->isAdmin() || $user->hasRole('admin');
    }

    public function verifyMemberEmail(User $user, SupportTicket $supportTicket): bool
    {
        return ($user->isAdmin() || $user->hasRole('admin'))
            && $supportTicket->category === SupportTicket::CATEGORY_EMAIL_CONFIRMATION
            && $supportTicket->user_id !== null;
    }
}
