<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function getPrivateFile($path)
    {
        $decryptPath = Crypt::decryptString($path);

        if (Storage::disk('private')->exists($decryptPath)) {
            $file = Storage::disk('private')->path($decryptPath);

            return response()->file($file);
        }
    }
}
