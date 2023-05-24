<?php

namespace App\Http\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * 
 */
trait ValidationErrorMessageTrait 
{
    /**
     * Validation Error Message
     * @return void
     * @throws HttpResponseException
     */
    public function failedValidation(Validator $validator)
    {
        throw (new HttpResponseException(
            response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => (new ValidationException($validator))->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY)
        ));
    }
}
