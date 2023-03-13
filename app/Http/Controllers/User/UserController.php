<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getUserProfile()
    {
        try {
            $userRole = User::where('id', Auth()->user()->id)->first()->roles->pluck('name')[0];

            $user = User::where('id', Auth()->user()->id);
            if ($userRole == 'doctor') {
                $user = $user->with(['doctorInfo']);
            } else if ($userRole == 'apotek_owner') {
                $user = $user->with(['apotekInfo']);
            }
            $user = $user->first();
            
            return response()->json([
                'status' => true,
                'message' => 'Get User profile data success',
                'data' => $user,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')');
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateUserProfile(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|min:5',
                    'email' => 'required|email',
                    'password' => 'nullable|min:8|confirmed',
                    'phone' => 'required|max:15',
                    'gender' => 'required|in:m,f',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user = User::where('id', Auth()->user()->id)->first();
            $fileSystem = new Filesystem();
            
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->gender = $request->gender;

            if(isset($request->photo)){
                if ($fileSystem->isFile($request->photo)) $user->photo = $request->file('photo');
            } else {
                $user->photo = null;
            }
            if (isset($request->password)) $user->password = $request->password;

            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Update User profile data success',
                'data' => $user,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error($th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')');
            return response()->json([
                'status' => false,
                'message' => $th->getMessage() . ' at ' . $th->getfile() . ' (Line: ' . $th->getLine() . ')',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
