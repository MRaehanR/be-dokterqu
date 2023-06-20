<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EmailVerificationController extends Controller
{
    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail())
            return response()->error('Already Verified', Response::HTTP_ALREADY_REPORTED);

        $request->user()->sendEmailVerificationNotification();

        return response()->success('Verification link sent', Response::HTTP_OK);
    }

    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail())
            return response()->error('Already Verified', Response::HTTP_ALREADY_REPORTED);

        if ($request->user()->markEmailAsVerified())
            event(new Verified($request->user()));

        return response()->success('Email has been verified', Response::HTTP_OK);
    }
}
