<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPassword\CheckResetCodeRequest;
use App\Http\Requests\ForgotPassword\ResetPasswordRequest;
use App\Http\Requests\ForgotPassword\SendResetCodeRequest;
use App\Mail\SendCodeResetPassword;
use App\Models\ResetCodePassword;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class ForgotPasswordController extends Controller
{
    public function sendResetCode(SendResetCodeRequest $request)
    {
        if (!User::firstWhere('email', $request->input('email')))
            return response()->error('Email not found', Response::HTTP_NOT_FOUND);

        $oldResetCode = ResetCodePassword::where('email', $request->email)->first();

        if ($oldResetCode) $oldResetCode->delete();

        $resetCode = mt_rand(0, 999999);

        ResetCodePassword::create([
            'email' => $request->email,
            'code' => $resetCode,
        ]);

        Mail::to($request->email)->send(new SendCodeResetPassword($resetCode));

        return response()->success('Reset password code sent on your email', Response::HTTP_OK);
    }

    public function checkResetCode(CheckResetCodeRequest $request)
    {
        $resetCode = ResetCodePassword::firstWhere('code', $request->input('code'));

        if ($request->input('email') !== $resetCode->email)
            return response()->error("Email not Match", Response::HTTP_FORBIDDEN);

        if ($resetCode->created_at > now()->addHour()) {
            $resetCode->delete();
            return response()->error("Reset Code is Expired", Response::HTTP_FORBIDDEN);
        }

        return response()->success("Reset Code is Valid", Response::HTTP_ACCEPTED);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $resetCode = ResetCodePassword::firstWhere('code', $request->input('code'));

        if ($resetCode->created_at > now()->addHour()) {
            $resetCode->delete();
            return response()->error("Reset Code is Expired", Response::HTTP_FORBIDDEN);
        }

        User::firstWhere('email', $resetCode->email)->update([
            'password' => $request->input('password'),
        ]);

        $resetCode->delete();

        return response()->success("Password has been successfully changed", Response::HTTP_OK);
    }
}
