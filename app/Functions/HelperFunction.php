<?php

namespace App\Functions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Functions\EncryptionFunction;

class HelperFunction
{
    public static function getAuthUserId()
    {
        // Auth::id() is a shortcut that directly returns the authenticated user's ID or null if not authenticated.
        return Auth::id();
    }

    public static function getUserEntryId($user)
    {
        return Auth::id() ?? $user->id;
    }

    public static function dataProcessor($data)
    {
        try {
            if (is_object($data)) {
                $data = $data->toArray();
            }
            if (is_array($data)) {
                $keysToModify = [
                    'id',
                    'user_id',
                    'updated_at',
                    'created_at',
                    'contact_image',
                    'avatar',
                    'country_id'
                ];
                foreach ($keysToModify as $key) {
                    if (in_array($key, array_keys($data))) {
                        if (in_array($key, ['updated_at', 'created_at'])) {
                            $data[$key] = date('Y-M-d', strtotime($data[$key]));
                        } elseif (in_array($key, ['id', 'user_id', 'country_id'])) {
                            $data[$key] = EncryptionFunction::encodeId($data[$key]);
                            if($data[$key] === false){
                                $data[$key] = null;
                            }
                        } elseif (in_array($key, ['contact_image', 'avatar'])) {
                            $data[$key] = asset($data[$key]);
                        }
                    }
                }
            }
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }
}
