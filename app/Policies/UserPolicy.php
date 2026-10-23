<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use HandlesAuthorization;

    public function follow(User $user, User $otherUser): Response
    {
        if ($user->getAuthIdentifier() !== $otherUser->getAuthIdentifier()) {
            return $this->allow();
        }

        return $this->deny('A user cannot follow itself');
    }
}
