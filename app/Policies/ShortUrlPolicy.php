<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\ShortUrl;
use App\Models\User;

class ShortUrlPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ShortUrl $shortUrl): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $shortUrl->company_id === $user->company_id;
        }

        if ($user->isMember()) {
            return $shortUrl->user_id !== $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, ShortUrl $shortUrl): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ($user->isAdmin()) {
            return $shortUrl->company_id === $user->company_id;
        }

        return false;
    }

    public function delete(User $user, ShortUrl $shortUrl): bool
    {
        return false;
    }

    public function restore(User $user, ShortUrl $shortUrl): bool
    {
        return false;
    }

    public function forceDelete(User $user, ShortUrl $shortUrl): bool
    {
        return false;
    }
}
