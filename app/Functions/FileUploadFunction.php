<?php

namespace App\Functions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Exception;
use Intervention\Image\Format;
use Intervention\Image\Laravel\Facades\Image;

class FileUploadFunction
{

    /**
     * Handle file upload
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string|null
     */
    public static function uploadFile(UploadedFile $file, string $directory): string
    {
        try {
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $directory . '/' . $fileName;
            $fullDirectory = public_path($directory);
            if (!is_dir($fullDirectory)) {
                mkdir($fullDirectory, 0755, true);
            }
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory, 0755, true);
            }
            $file->move($fullDirectory, $fileName);
            return $filePath;
        } catch (Exception $e) {
            Log::error('Exception occurred [FUPF-100]', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return '';
        }
    }

    /**
     * Handle file (image) upload
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string|null
     */
    public static function uploadImageFile(UploadedFile $file, string $directory, int $maxWidth = 300, int $maxHeight = 200): string
    {
        try {
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $directory . '/' . $fileName;
            $fullDirectory = public_path($directory);
            if (!is_dir($fullDirectory)) {
                mkdir($fullDirectory, 0755, true);
            }
            $image = Image::read($file);

            $image->scale($maxWidth, $maxHeight);

            $image->save($fullDirectory . '/' . $fileName);

            return $filePath;
        } catch (Exception $e) {
            Log::error('Exception occurred [FUPF-101]', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return '';
        }
    }
}
