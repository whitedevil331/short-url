@extends('layouts.app')

@section('title', 'Invite User')

@section('content')
<div class="card">
    <h2>Invite User</h2>
    <form method="POST" action="{{ route('invitations.store') }}" style="margin-top: 20px;">
        @csrf
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="8">
        </div>
        <div class="form-group">
            <label for="role_id">Role</label>
            <select id="role_id" name="role_id" required>
                <option value="">Select a role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @if(auth()->user()->isSuperAdmin())
            @php
                $selectedRole = old('role_id') ? \App\Models\Role::find(old('role_id')) : null;
                $isAdminRole = $selectedRole && $selectedRole->slug === 'admin';
            @endphp
            <div class="form-group" id="company-name-group" style="display: {{ $isAdminRole ? 'block' : 'none' }};">
                <label for="company_name">Company Name <span style="color: red;">*</span></label>
                <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" placeholder="Enter company name" {{ $isAdminRole ? 'required' : '' }}>
                <small style="color: #666; display: block; margin-top: 5px;">
                    Enter a company name to create a new company for this Admin.
                </small>
            </div>
            <div class="form-group" id="company-select-group" style="display: {{ $isAdminRole ? 'none' : 'block' }};">
                <label for="company_id">Company (Optional)</label>
                <select id="company_id" name="company_id">
                    <option value="">No Company</option>
                    @if(isset($companies))
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }} ({{ $company->users_count }} users)
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>
            <script>
                document.getElementById('role_id').addEventListener('change', function() {
                    const roleSelect = this;
                    const selectedOption = roleSelect.options[roleSelect.selectedIndex];
                    const companyNameGroup = document.getElementById('company-name-group');
                    const companySelectGroup = document.getElementById('company-select-group');
                    const companyNameInput = document.getElementById('company_name');
                    
                    if (selectedOption.text.toLowerCase().includes('admin')) {
                        companyNameGroup.style.display = 'block';
                        companySelectGroup.style.display = 'none';
                        companyNameInput.setAttribute('required', 'required');
                    } else {
                        companyNameGroup.style.display = 'none';
                        companySelectGroup.style.display = 'block';
                        companyNameInput.removeAttribute('required');
                    }
                });
                
                // Trigger on page load if role is already selected
                if (document.getElementById('role_id').value) {
                    document.getElementById('role_id').dispatchEvent(new Event('change'));
                }
            </script>
        @endif
        <button type="submit" class="btn">Invite User</button>
    </form>
</div>
@endsection

