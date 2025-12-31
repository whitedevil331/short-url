<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public const SUPER_ADMIN = 'superadmin';
    public const ADMIN = 'admin';
    public const MEMBER = 'member';
    public const SALES = 'sales';
    public const MANAGER = 'manager';
}
