<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class FormController extends Controller
{
    public function registerCustomer()
    {
        try {
            return response()->json([
                [
                    'label' => 'Name',
                    'name' => 'name',
                    'type' => 'text',
                    'required' => true,
                    'prepend_inner_icon' => 'mdi-account-circle',
                ],
                [
                    'label' => 'Email',
                    'name' => 'email',
                    'type' => 'email',
                    'required' => true,
                    'prepend_inner_icon' => 'mdi-email',
                ],
                [
                    'label' => 'Password',
                    'name' => 'password',
                    'type' => 'password',
                    'required' => true,
                ],
                [
                    'label' => 'Confirmation Password',
                    'name' => 'password_confirmation',
                    'type' => 'password',
                    'required' => true,
                ],
                [
                    'label' => 'Phone',
                    'name' => 'phone',
                    'type' => 'phone',
                    'required' => true,
                    'prepend_inner_icon' => 'mdi-phone',
                ],
                [
                    'label' => 'Photo Profile',
                    'name' => 'photo',
                    'type' => 'file',
                    'required' => false,
                ],
                [
                    'label' => 'Gender',
                    'name' => 'gender',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        [
                            'label' => 'Male',
                            'value' => 'm',
                        ],
                        [
                            'label' => 'Female',
                            'value' => 'f',
                        ],
                    ]
                ],
                [
                    'label' => 'Role',
                    'name' => 'role',
                    'type' => 'hidden',
                    'required' => true,
                    'value' => 3
                ],
            ]);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
