<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->role->value === UserRole::ADMIN->value || $user->id === $comment->user_id;
    }


    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->role->value === UserRole::ADMIN->value || $user->id === $comment->user_id;
    }
}
