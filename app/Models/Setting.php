<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key', 'value'
    ];

    public static function getAllCached()
    {
        return Cache::rememberForever('settings.all', function () {
            return self::all()->pluck('value', 'key')->toArray();
        });
    }

    public static function getValue(string $key, $default = null): string
    {
        return self::getAllCached()[$key] ?? $default;
    }
}
