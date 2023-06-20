<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_send_email_verification_success()
    {
        $user = User::factory()->roleCustomer()->unverified()->create();

        $response = $this->actingAs($user)->post('api/auth/email/send-verification');

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_send_email_verification_with_verified_account()
    {
        $user = User::factory()->roleCustomer()->create();

        $response = $this->actingAs($user)->post('api/auth/email/send-verification');

        $response->assertStatus(Response::HTTP_ALREADY_REPORTED);
    }

    public function test_verify_email_with_verified_account()
    {
        $user = User::factory()->roleCustomer()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertStatus(Response::HTTP_ALREADY_REPORTED);
    }

    public function test_verify_email_success()
    {
        $user = User::factory()->roleCustomer()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->assertSame(null, $user->email_verified_at);

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertNotNull($user->email_verified_at);

        $response->assertStatus(Response::HTTP_OK);
    }
}
