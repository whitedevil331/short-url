<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Role;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShortUrlTest extends TestCase
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

    public function test_admin_can_create_short_urls(): void
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

        $response = $this->actingAs($admin)->post('/short-urls', [
            'original_url' => 'https://example.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('short_urls', [
            'original_url' => 'https://example.com',
            'company_id' => $company->id,
            'user_id' => $admin->id,
        ]);
    }

    public function test_member_cannot_create_short_urls(): void
    {
        $company = Company::create(['name' => 'Test Company', 'slug' => 'test-company']);
        $memberRole = Role::where('slug', 'member')->first();
        $member = User::create([
            'name' => 'Member User',
            'email' => 'member@test.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role_id' => $memberRole->id,
        ]);

        $response = $this->actingAs($member)->post('/short-urls', [
            'original_url' => 'https://example.com',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('short_urls', ['original_url' => 'https://example.com']);
    }

    public function test_superadmin_cannot_create_short_urls(): void
    {
        $superAdminRole = Role::where('slug', 'superadmin')->first();
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $superAdminRole->id,
        ]);

        $response = $this->actingAs($superAdmin)->post('/short-urls', [
            'original_url' => 'https://example.com',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('short_urls', ['original_url' => 'https://example.com']);
    }

    public function test_admin_can_only_see_short_urls_from_own_company(): void
    {
        $company1 = Company::create(['name' => 'Company 1', 'slug' => 'company-1']);
        $company2 = Company::create(['name' => 'Company 2', 'slug' => 'company-2']);
        
        $adminRole = Role::where('slug', 'admin')->first();
        $salesRole = Role::where('slug', 'sales')->first();
        
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'company_id' => $company1->id,
            'role_id' => $adminRole->id,
        ]);

        $sales1 = User::create([
            'name' => 'Sales 1',
            'email' => 'sales1@test.com',
            'password' => bcrypt('password'),
            'company_id' => $company1->id,
            'role_id' => $salesRole->id,
        ]);

        $sales2 = User::create([
            'name' => 'Sales 2',
            'email' => 'sales2@test.com',
            'password' => bcrypt('password'),
            'company_id' => $company2->id,
            'role_id' => $salesRole->id,
        ]);

        ShortUrl::create([
            'user_id' => $sales1->id,
            'company_id' => $company1->id,
            'original_url' => 'https://company1.com',
            'short_code' => 'abc12345',
        ]);

        ShortUrl::create([
            'user_id' => $sales2->id,
            'company_id' => $company2->id,
            'original_url' => 'https://company2.com',
            'short_code' => 'xyz67890',
        ]);

        $response = $this->actingAs($admin)->get('/short-urls');

        $response->assertStatus(200);
        $response->assertSee('https://company1.com');
        $response->assertDontSee('https://company2.com');
    }

    public function test_member_can_only_see_short_urls_not_created_by_themselves(): void
    {
        $company = Company::create(['name' => 'Test Company', 'slug' => 'test-company']);
        
        $memberRole = Role::where('slug', 'member')->first();
        $salesRole = Role::where('slug', 'sales')->first();
        
        $member = User::create([
            'name' => 'Member User',
            'email' => 'member@test.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role_id' => $memberRole->id,
        ]);

        $sales = User::create([
            'name' => 'Sales User',
            'email' => 'sales@test.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role_id' => $salesRole->id,
        ]);

        ShortUrl::create([
            'user_id' => $member->id,
            'company_id' => $company->id,
            'original_url' => 'https://member-url.com',
            'short_code' => 'member123',
        ]);

        ShortUrl::create([
            'user_id' => $sales->id,
            'company_id' => $company->id,
            'original_url' => 'https://sales-url.com',
            'short_code' => 'sales456',
        ]);

        $response = $this->actingAs($member)->get('/short-urls');

        $response->assertStatus(200);
        $response->assertDontSee('https://member-url.com');
        $response->assertSee('https://sales-url.com');
    }

    public function test_short_urls_are_not_publicly_resolvable(): void
    {
        $company = Company::create(['name' => 'Test Company', 'slug' => 'test-company']);
        $salesRole = Role::where('slug', 'sales')->first();
        
        $sales = User::create([
            'name' => 'Sales User',
            'email' => 'sales@test.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role_id' => $salesRole->id,
        ]);

        $shortUrl = ShortUrl::create([
            'user_id' => $sales->id,
            'company_id' => $company->id,
            'original_url' => 'https://example.com',
            'short_code' => 'test1234',
        ]);

        $response = $this->get('/s/' . $shortUrl->short_code);

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_resolve_short_url(): void
    {
        $company = Company::create(['name' => 'Test Company', 'slug' => 'test-company']);
        $salesRole = Role::where('slug', 'sales')->first();
        
        $sales = User::create([
            'name' => 'Sales User',
            'email' => 'sales@test.com',
            'password' => bcrypt('password'),
            'company_id' => $company->id,
            'role_id' => $salesRole->id,
        ]);

        $shortUrl = ShortUrl::create([
            'user_id' => $sales->id,
            'company_id' => $company->id,
            'original_url' => 'https://example.com',
            'short_code' => 'test1234',
        ]);

        $response = $this->actingAs($sales)->get('/s/' . $shortUrl->short_code);

        $response->assertRedirect('https://example.com');
        $this->assertEquals(1, $shortUrl->fresh()->clicks);
    }

    public function test_superadmin_can_see_all_short_urls(): void
    {
        $company1 = Company::create(['name' => 'Company 1', 'slug' => 'company-1']);
        $company2 = Company::create(['name' => 'Company 2', 'slug' => 'company-2']);
        
        $superAdminRole = Role::where('slug', 'superadmin')->first();
        $salesRole = Role::where('slug', 'sales')->first();
        
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $superAdminRole->id,
        ]);

        $sales1 = User::create([
            'name' => 'Sales 1',
            'email' => 'sales1@test.com',
            'password' => bcrypt('password'),
            'company_id' => $company1->id,
            'role_id' => $salesRole->id,
        ]);

        $sales2 = User::create([
            'name' => 'Sales 2',
            'email' => 'sales2@test.com',
            'password' => bcrypt('password'),
            'company_id' => $company2->id,
            'role_id' => $salesRole->id,
        ]);

        ShortUrl::create([
            'user_id' => $sales1->id,
            'company_id' => $company1->id,
            'original_url' => 'https://company1.com',
            'short_code' => 'abc12345',
        ]);

        ShortUrl::create([
            'user_id' => $sales2->id,
            'company_id' => $company2->id,
            'original_url' => 'https://company2.com',
            'short_code' => 'xyz67890',
        ]);

        $response = $this->actingAs($superAdmin)->get('/short-urls');

        $response->assertStatus(200);
        $response->assertSee('https://company1.com');
        $response->assertSee('https://company2.com');
    }
}
