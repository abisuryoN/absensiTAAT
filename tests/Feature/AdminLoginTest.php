<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run only necessary seeders for authentication
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    public function test_admin_can_login_with_correct_credentials(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin SMAN 1 Tajurhalang',
            'email' => 'admin@sman1tajurhalang.sch.id',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $admin->assignRole('super_admin');

        $response = $this->post('/login', [
            'email' => 'admin@sman1tajurhalang.sch.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_cannot_login_with_incorrect_password(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin SMAN 1 Tajurhalang',
            'email' => 'admin@sman1tajurhalang.sch.id',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $admin->assignRole('super_admin');

        $response = $this->post('/login', [
            'email' => 'admin@sman1tajurhalang.sch.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_admin_cannot_login(): void
    {
        // Create inactive admin user
        $admin = User::create([
            'name' => 'Admin SMAN 1 Tajurhalang',
            'email' => 'admin@sman1tajurhalang.sch.id',
            'password' => Hash::make('password123'),
            'is_active' => false, // Inactive
        ]);
        $admin->assignRole('super_admin');

        $response = $this->post('/login', [
            'email' => 'admin@sman1tajurhalang.sch.id',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_nonexistent_email_cannot_login(): void
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'anypassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_email_must_be_valid_format(): void
    {
        $response = $this->post('/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
