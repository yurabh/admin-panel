<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Page $page): bool
    {
        return $user->id === $page->user_id;
    }
}
