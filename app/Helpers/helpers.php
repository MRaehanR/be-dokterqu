<?php

namespace App\Helpers;

use Carbon\Carbon;

if (!function_exists('storeImageToPublic')) {
    function storeImageToPublic($file, String $path)
    {
        if ($file) {
            $fileName = Carbon::now()->format('YmdHis') . "_" . md5_file($file) . "." . $file->getClientOriginalExtension();
            $filePath = "storage/images/$path/" . $fileName;
            $file->storeAs(
                "public/images/$path",
                $fileName
            );
            return $filePath;
        }
        return null;
    }
}
