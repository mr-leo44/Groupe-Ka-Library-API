<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialUserContract;

trait SeedsRoles
{
    public function seedRoles(): void
    {
        $this->seed(RoleSeeder::class);
    }
}

class AuthTest extends TestCase
{
    use RefreshDatabase, SeedsRoles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedRoles();
    }
    /**
     * Test the full register and login flow.
     */
    public function test_register_and_login_flow(): void
    {
        // Register
        $registerResponse = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $registerResponse->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user',
                    'token',
                ]
            ]);

        // Login
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user',
                    'token',
                ]
            ]);
    }

    /** @test */
    public function test_user_cannot_register_with_existing_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test',
            'email' => 'taken@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['email'],
            ])
            ->assertJsonPath('message', 'The email has already been taken.')
            ->assertJsonPath('errors.email.0', 'The email has already been taken.');
    }

    /** @test */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user',
                    'token',
                ]
            ]);
    }

    /** @test */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure([
                'message',
            ])
            ->assertJsonPath('message', 'Invalid credentials');
    }

    /**
     * Test social login with Google.
     */
    public function test_social_login_google(): void
    {
        // Mock the Socialite user
        $socialUser = Mockery::mock(SocialUserContract::class);
        $socialUser->shouldReceive('getId')->andReturn('12345');
        $socialUser->shouldReceive('getEmail')->andReturn('social@example.com');
        $socialUser->shouldReceive('getName')->andReturn('Social User');
        $socialUser->shouldReceive('getNickname')->andReturn(null);
        $socialUser->shouldReceive('getAvatar')->andReturn(null);

        // Mock the Socialite driver chain
        Socialite::shouldReceive('driver->stateless->userFromToken')
            ->andReturn($socialUser);

        $response = $this->postJson('/api/auth/social', [
            'provider' => 'google',
            'access_token' => 'token',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user',
                    'token',
                ]
            ]);
    }

    /**
     * Tear down Mockery.
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function test_social_login_fails_with_invalid_provider(): void
    {
        $response = $this->postJson('/api/auth/social/invalid', [
            'token' => 'whatever',
        ]);

        $response->assertStatus(404)
            ->assertJsonStructure([
                'message',
            ])
            ->assertJsonPath('message', 'The route api/auth/social/invalid could not be found.');
    }
}
