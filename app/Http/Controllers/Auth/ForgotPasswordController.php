<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SendCodeResetPassword;
use App\Models\ResetCodePassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ForgotPasswordController extends Controller
{
    public function forgot(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => "Validation Error",
                    "errors" => $validator->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (!User::where('email', $request->input('email'))) {
                return response()->json([
                    "status" => false,
                    "message" => "Email not found.",
                ], Response::HTTP_NOT_FOUND);
            }

            $oldResetCode = ResetCodePassword::where('email', $request->email)->first();
            if ($oldResetCode) $oldResetCode->delete();

            $resetCode = mt_rand(0, 999999);
            ResetCodePassword::create([
                'email' => $request->email,
                'code' => $resetCode,
            ]);

            Mail::to($request->email)->send(new SendCodeResetPassword($resetCode));

            return response()->json([
                "status" => true,
                "message" => "Reset password code sent on your email.",
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkResetCode(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|exists:reset_code_passwords',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => "Validation Error",
                    "errors" => $validator->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $resetCode = ResetCodePassword::firstWhere('code', $request->code);
            if ($resetCode->created_at > now()->addHour()) {
                $resetCode->delete();
                return response()->json([
                    "status" => false,
                    "message" => "Code is Expired",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return response()->json([
                "status" => true,
                "message" => "Code is Valid",
                "data" => $resetCode,
            ], Response::HTTP_ACCEPTED);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function reset(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'code' => 'required|exists:reset_code_passwords',
                'password' => 'required|string|confirmed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => "Validation Error",
                    "errors" => $validator->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $resetCode = ResetCodePassword::firstWhere('code', $request->code);
            if ($resetCode->created_at > now()->addHour()) {
                $resetCode->delete();
                return response()->json([
                    "status" => false,
                    "message" => "Code is Expired",
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user = User::firstWhere('email', $resetCode->email);
            $user->password = $request->input('password');
            $user->save();
            $resetCode->delete();

            return response()->json([
                "status" => true,
                "message" => "Password has been successfully changed",
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
