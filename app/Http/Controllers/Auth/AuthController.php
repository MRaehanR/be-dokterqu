<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->error('Account not found.', Response::HTTP_NOT_FOUND);
        }

        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response()->error('Email or Password does not match.', Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->active) {
            $role = $user->roles->first()->name;
            return response()->error((in_array($role, [User::TYPE_DOCTOR, User::TYPE_APOTEK_OWNER])) ? 'Your data has not been verified' : 'Your Account is Disabled', Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('access_token')->plainTextToken;

        return response()->success(
            'User Logged In Successfully',
            Response::HTTP_CREATED,
            [
                'user' => new UserResource($user),
                'access_token' => $token,
            ]
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->success('User Logout Successfully', Response::HTTP_OK);
    }

    public function register(RegisterRequest $request)
    {
        $this->userService->createUser($request->all());

        return response()->success('User Register Successfully', Response::HTTP_CREATED);
    }
}
