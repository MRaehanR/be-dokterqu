<?php

namespace Tests\Feature\Auth;

use App\Models\ResetCodePassword;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use DatabaseTransactions;

    public function test_send_reset_code_success()
    {
        $user = User::factory()->roleCustomer()->create();

        $response = $this->post('api/auth/password/send-reset-code', [
            'email' => $user->email,
        ]);

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_send_reset_code_email_not_found()
    {
        $response = $this->post('api/auth/password/send-reset-code', [
            'email' => 'test@test.com',
        ]);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_check_reset_code_success()
    {
        $user = User::factory()->roleCustomer()->create();

        $response = $this->actingAs($user)->post('api/auth/password/send-reset-code', [
            'email' => $user->email,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $resetCode = ResetCodePassword::firstWhere('email', $user->email)->code;

        $response = $this->actingAs($user)->post('api/auth/password/check-reset-code', [
            'email' => $user->email,
            'code' => $resetCode,
        ]);

        $response->assertStatus(Response::HTTP_ACCEPTED);
    }

    public function test_check_reset_code_email_not_match()
    {
        $user = User::factory()->roleCustomer()->create();

        $response = $this->actingAs($user)->post('api/auth/password/send-reset-code', [
            'email' => $user->email,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $resetCode = ResetCodePassword::firstWhere('email', $user->email)->code;

        $response = $this->actingAs($user)->post('api/auth/password/check-reset-code', [
            'email' => 'test@test.com',
            'code' => $resetCode,
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_check_reset_code_expired()
    {
        $user = User::factory()->roleCustomer()->create();

        $response = $this->actingAs($user)->post('api/auth/password/send-reset-code', [
            'email' => $user->email,
        ]);

        $response->assertStatus(Response::HTTP_OK);

        $resetCode = ResetCodePassword::firstWhere('email', $user->email);

        $resetCode->created_at = now()->addDay();
        $resetCode->save();

        $resetCode = $resetCode->code;

        $response = $this->actingAs($user)->post('api/auth/password/check-reset-code', [
            'email' => $user->email,
            'code' => $resetCode,
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
