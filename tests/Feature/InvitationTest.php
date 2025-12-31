<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }

    private function seedRoles(): void
    {
        Role::create(['name' => 'SuperAdmin', 'slug' => 'superadmin']);
        Role::create(['name' => 'Admin', 'slug' => 'admin']);
        Role::create(['name' => 'Member', 'slug' => 'member']);
        Role::create(['name' => 'Sales', 'slug' => 'sales']);
        Role::create(['name' => 'Manager', 'slug' => 'manager']);
    }

    public function test_superadmin_can_create_company_when_inviting_admin(): void
    {
        $superAdminRole = Role::where('slug', 'superadmin')->first();
        $adminRole = Role::where('slug', 'admin')->first();
        
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $superAdminRole->id,
        ]);

        $response = $this->actingAs($superAdmin)->post('/invitations', [
            'name' => 'New Admin',
            'email' => 'newadmin@test.com',
            'password' => 'password123',
            'role_id' => $adminRole->id,
            'company_name' => 'New Company Name',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'newadmin@test.com']);
        $company = Company::where('name', 'New Company Name')->first();
        $this->assertNotNull($company);
        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@test.com',
            'company_id' => $company->id,
        ]);
    }

    public function test_superadmin_can_invite_admin_with_new_company(): void
    {
        $superAdminRole = Role::where('slug', 'superadmin')->first();
        $adminRole = Role::where('slug', 'admin')->first();
        
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $superAdminRole->id,
        ]);

        $response = $this->actingAs($superAdmin)->post('/invitations', [
            'name' => 'New Admin',
            'email' => 'newadmin@test.com',
            'password' => 'password123',
            'role_id' => $adminRole->id,
            'company_name' => 'Another New Company',
        ]);

        $response->assertRedirect();
        $company = Company::where('name', 'Another New Company')->first();
        $this->assertNotNull($company);
        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@test.com',
            'company_id' => $company->id,
            'role_id' => $adminRole->id,
        ]);
    }

    public function test_admin_can_invite_admin_in_own_company(): void
    {
        $company = Company::create(['name' => 'Test Company', 'slug' => 'test-company']);
        
        $adminRole = Role::where('slug', 'admin')->first();
        
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role_id' => $adminRole->id,
        ]);

        $response = $this->actingAs($admin)->post('/invitations', [
            'name' => 'Another Admin',
            'email' => 'anotheradmin@test.com',
            'password' => 'password123',
            'role_id' => $adminRole->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'anotheradmin@test.com',
            'company_id' => $company->id,
            'role_id' => $adminRole->id,
        ]);
    }

    public function test_admin_can_invite_admin_in_other_company(): void
    {
        $company1 = Company::create(['name' => 'Company 1', 'slug' => 'company-1']);
        $company2 = Company::create(['name' => 'Company 2', 'slug' => 'company-2']);
        
        $adminRole = Role::where('slug', 'admin')->first();
        
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'company_id' => $company1->id,
            'role_id' => $adminRole->id,
        ]);

        $response = $this->actingAs($admin)->post('/invitations', [
            'name' => 'Another Admin',
            'email' => 'anotheradmin@test.com',
            'password' => 'password123',
            'role_id' => $adminRole->id,
            'company_id' => $company2->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'anotheradmin@test.com',
            'company_id' => $company2->id,
            'role_id' => $adminRole->id,
        ]);
    }

    public function test_admin_cannot_invite_member_in_own_company(): void
    {
        $company = Company::create(['name' => 'Test Company', 'slug' => 'test-company']);
        
        $adminRole = Role::where('slug', 'admin')->first();
        $memberRole = Role::where('slug', 'member')->first();
        
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role_id' => $adminRole->id,
        ]);

        $response = $this->actingAs($admin)->post('/invitations', [
            'name' => 'New Member',
            'email' => 'member@test.com',
            'password' => 'password123',
            'role_id' => $memberRole->id,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'member@test.com']);
    }

    public function test_admin_can_invite_sales_in_own_company(): void
    {
        $company = Company::create(['name' => 'Test Company', 'slug' => 'test-company']);
        
        $adminRole = Role::where('slug', 'admin')->first();
        $salesRole = Role::where('slug', 'sales')->first();
        
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role_id' => $adminRole->id,
        ]);

        $response = $this->actingAs($admin)->post('/invitations', [
            'name' => 'New Sales',
            'email' => 'sales@test.com',
            'password' => 'password123',
            'role_id' => $salesRole->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'sales@test.com',
            'company_id' => $company->id,
            'role_id' => $salesRole->id,
        ]);
    }

    public function test_superadmin_can_invite_sales_in_new_company(): void
    {
        $superAdminRole = Role::where('slug', 'superadmin')->first();
        $salesRole = Role::where('slug', 'sales')->first();
        
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $superAdminRole->id,
        ]);

        $company = Company::create(['name' => 'New Company', 'slug' => 'new-company']);

        $response = $this->actingAs($superAdmin)->post('/invitations', [
            'name' => 'New Sales',
            'email' => 'newsales@test.com',
            'password' => 'password123',
            'role_id' => $salesRole->id,
            'company_id' => $company->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'newsales@test.com',
            'company_id' => $company->id,
            'role_id' => $salesRole->id,
        ]);
    }
}
