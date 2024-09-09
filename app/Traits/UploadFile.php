<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait UploadFile
{

    public function handleFileUpload(Request $request, $fileKey, $folder)
    {
        // make sure the file exist then store it
        if ($request->hasFile($fileKey)) {
            return $request->file($fileKey)->store($folder, 'public');
        }

        return null;
    }
}
