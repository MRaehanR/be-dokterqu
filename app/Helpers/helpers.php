<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (!function_exists('storeTo')) {
    function storeTo(String $disk, String $path, $file, $userId = null)
    {
        if ($file) {
            $uniqueString = ($userId) ?? Str::random(16);

            $fileName = Carbon::now()->format('YmdHis') . "_" . $uniqueString . "." . $file->getClientOriginalExtension();

            $filePath = ($disk === "private")
                ? Crypt::encryptString("$path/$fileName")
                : "storage/$path/" . $fileName;

            Storage::disk($disk)->putFileAs($path, $file, $fileName);

            return $filePath;
        }
        return null;
    }
}
