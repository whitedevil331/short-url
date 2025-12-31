<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

class InvitationService
{
    public function canInvite(User $inviter, string $roleSlug, ?int $companyId = null): bool
    {
        if ($inviter->isSuperAdmin()) {
            return true;
        }

        if ($inviter->isAdmin()) {
            // $targetCompanyId = $companyId ?? $inviter->company_id;
            // if ($targetCompanyId === $inviter->company_id) {
            //     return !in_array($roleSlug, [Role::MEMBER]);
            // }
            return true;
        }

        return false;
    }

    public function createUserFromInvitation(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }
}

