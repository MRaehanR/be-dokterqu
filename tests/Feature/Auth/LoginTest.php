<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use DatabaseTransactions;
    
    public function test_login_success(): void
    {
        $user = User::factory()->roleCustomer()->create([
            'active' => true,
        ]);

        $response = $this->post('api/auth/login', [
            'email' => $user['email'],
            'password' => 'op[kl;m,.',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user' => [
                    'name',
                    'email',
                    'email_verified',
                    'photo',
                    'phone',
                    'active',
                    'gender',
                    'role',
                ],
                'access_token',
            ],
        ]);
    }

    public function test_login_failed_account_not_found(): void
    {
        $response = $this->post('api/auth/login', [
            'email' => 'user@dokterqu.com',
            'password' => 'op[kl;m,.',
        ]);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $response->assertJsonStructure(['status', 'message',]);
        $response->assertJson([
            'status' => false,
            'message' => 'Account not found.'
        ]);
    }

    public function test_login_failed_validation_error(): void
    {
        $response = $this->post('api/auth/login', []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure(['status', 'message', 'errors',]);
    }

    public function test_login_failed_with_wrong_password(): void
    {
        $user = User::factory()->roleCustomer()->create();

        $response = $this->post('api/auth/login', [
            'email' => $user['email'],
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJsonStructure(['status', 'message',]);
        $response->assertJson([
            'status' => false,
            'message' => 'Email or Password does not match.',
        ]);
    }

    public function test_login_failed_with_disabled_account_and_customer_role(): void
    {
        $user = User::factory()->roleCustomer()->create([
            'active' => false,
        ]);

        $response = $this->post('api/auth/login', [
            'email' => $user['email'],
            'password' => 'op[kl;m,.',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJsonStructure(['status', 'message',]);
        $response->assertJson([
            'status' => false,
            'message' => 'Your Account is Disabled',
        ]);
    }

    public function test_login_failed_with_disabled_account_and_doctor_role(): void
    {
        $user = User::create([
            'name' => 'Example',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => 'op[kl;m,.',
            'phone' => '0882383274',
            'gender' => 'm',
            'active' => false,
        ]);

        $user->assignRole('doctor');

        $response = $this->post('api/auth/login', [
            'email' => $user['email'],
            'password' => 'op[kl;m,.',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJsonStructure(['status', 'message',]);
        $response->assertJson([
            'status' => false,
            'message' => 'Your data has not been verified',
        ]);
    }

    public function test_login_failed_with_disabled_account_and_apotek_owner_role(): void
    {
        $user = User::create([
            'name' => 'Example',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => 'op[kl;m,.',
            'phone' => '0882383274',
            'gender' => 'm',
            'active' => false,
        ]);

        $user->assignRole('apotek_owner');

        $response = $this->post('api/auth/login', [
            'email' => $user['email'],
            'password' => 'op[kl;m,.',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJsonStructure(['status', 'message',]);
        $response->assertJson([
            'status' => false,
            'message' => 'Your data has not been verified',
        ]);
    }
}
