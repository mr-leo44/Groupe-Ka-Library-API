<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
    }

    /** @test */
    public function test_user_can_register_with_valid_data()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!@',
            'password_confirmation' => 'SecurePass123!@',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => ['user', 'token']
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com'
        ]);
    }

    /** @test */
    public function registration_requires_strong_password()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('password');
    }

    /** @test */
    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('SecurePass123!@'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'SecurePass123!@',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['user', 'token']
                 ]);
    }

    /** @test */
    public function login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('SecurePass123!@'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Invalid credentials'
                 ]);
    }

    /** @test */
    public function login_is_rate_limited()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('SecurePass123!@'),
        ]);

        // Attempt login 6 times with wrong password
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/auth/login', [
                'email' => 'john@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->postJson('/api/auth/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/auth/logout');

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Token should be deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    /** @test */
    public function test_user_can_access_profile_with_valid_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->getJson('/api/user/profile');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'email' => $user->email
                     ]
                 ]);
    }

    /** @test */
    public function test_user_cannot_access_profile_without_token()
    {
        $response = $this->getJson('/api/user/profile');

        $response->assertStatus(401);
    }

    /** @test */
    public function test_user_can_change_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPass123!@'),
        ]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/user/change-password', [
                             'current_password' => 'OldPass123!@',
                             'password' => 'NewPass123!@',
                             'password_confirmation' => 'NewPass123!@',
                         ]);

        $response->assertStatus(200);

        // Verify new password works
        $this->assertTrue(Hash::check('NewPass123!@', $user->fresh()->password));
    }

    /** @test */
    public function test_user_assigned_member_role_on_registration()
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!@',
            'password_confirmation' => 'SecurePass123!@',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        
        $this->assertTrue($user->hasRole('member'));
    }

    /** @test */
    public function last_login_tracking_works()
    {
        $user = User::factory()->create([
            'password' => Hash::make('SecurePass123!@'),
        ]);

        $this->assertNull($user->last_login_at);

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'SecurePass123!@',
        ]);

        $user->refresh();
        
        $this->assertNotNull($user->last_login_at);
        $this->assertNotNull($user->last_login_ip);
    }
}