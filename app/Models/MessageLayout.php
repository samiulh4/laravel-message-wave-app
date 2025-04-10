<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Functions\HelperFunction;

class MessageLayout extends Model
{
    protected $table = 'message_layouts';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];

    protected static function booted()
    {
        static::created(function ($data) {
            $data->created_by = HelperFunction::getAuthUserId();
            $data->updated_by = HelperFunction::getAuthUserId();
            $data->saveQuietly(); // Use saveQuietly to avoid triggering another save event
        });
        static::saving(function ($data) {
            $data->updated_by = HelperFunction::getAuthUserId(); // Update the `updated_by` field during any save
        });
    }
}
