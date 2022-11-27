<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ForgotPasswordController extends Controller
{
    public function forgot(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);
    
            if($validator->fails()){
                return response()->json([
                    "status" => false,
                    "message" => "Validation Error",
                    "errors" => $validator->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
    
            Password::sendResetLink($request->only('email'));
    
            return response()->json([
                "status" => true,
                "message" => "Reset password link sent on your email.",
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function reset(Request $request) {
        try {
            $validator = Validator::make($request->all(),[
                'email' => 'required|email',
                'token' => 'required|string',
                'password' => 'required|string'
            ]);

            if($validator->fails()){
                return response()->json([
                    "status" => false,
                    "message" => "Validation Error",
                    "errors" => $validator->errors(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
    
            $reset_password_status = Password::reset($request->only(['email', 'token', 'password']), function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            });
    
            if ($reset_password_status == Password::INVALID_TOKEN) {
                return response()->json([
                    "status" => false,
                    "message" => "Invalid token provided",
                ], Response::HTTP_BAD_REQUEST);
            }
    
            return response()->json([
                "status" => true,
                "message" => "Password has been successfully changed",
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
