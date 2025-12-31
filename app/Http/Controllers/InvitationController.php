<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    public function __construct(
        private InvitationService $invitationService
    ) {}

    public function create()
    {
        $roles = Role::whereNotIn('slug', [Role::SUPER_ADMIN])->get();
        
        $companies = null;
        if (Auth::user()->isSuperAdmin()) {
            $companies = \App\Models\Company::withCount('users')
                ->get()
                ->filter(function ($company) {
                    return $company->users_count > 0;
                })
                ->values();
        }
        
        return view('invitations.create', compact('roles', 'companies'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $role = Role::findOrFail($request->role_id);
        
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'company_name' => ['nullable', 'string', 'max:255'],
        ];
        
        if ($user->isSuperAdmin() && $role->slug === Role::ADMIN) {
            $rules['company_name'] = ['required', 'string', 'max:255'];
        }
        
        $validated = $request->validate($rules);
        
        $companyId = null;
        if ($user->isSuperAdmin() && $role->slug == Role::ADMIN) {
            if (!empty($validated['company_name'])) {
                $baseSlug = \Illuminate\Support\Str::slug($validated['company_name']);
                $slug = $baseSlug;
                $counter = 1;
                while (\App\Models\Company::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                $company = \App\Models\Company::create([
                    'name' => $validated['company_name'],
                    'slug' => $slug,
                ]);
                $companyId = $company->id;
            }
        } elseif ($user->isSuperAdmin()) {
            $companyId = $validated['company_id'] ?? null;
        } else {
            $companyId = $validated['company_id'] ?? $user->company_id;
        }

        if (!$this->invitationService->canInvite($user, $role->slug, $companyId)) {
            abort(403, 'You are not authorized to invite this role.');
        }

        $this->invitationService->createUserFromInvitation([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role_id' => $validated['role_id'],
            'company_id' => $companyId,
        ]);

        return redirect()->route('invitations.create')
            ->with('success', 'User invited successfully.');
    }
}
