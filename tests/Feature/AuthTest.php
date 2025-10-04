<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use Database\Seeders\RoleSeeder;
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
}
