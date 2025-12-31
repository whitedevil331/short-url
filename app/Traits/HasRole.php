<?php

namespace App\Traits;

use App\Models\Role;

trait HasRole
{
    public function hasRole(string $roleSlug): bool
    {
        return $this->role && $this->role->slug === $roleSlug;
    }

    public function hasAnyRole(array $roleSlugs): bool
    {
        if (!$this->role) {
            return false;
        }

        return in_array($this->role->slug, $roleSlugs);
    }
}

