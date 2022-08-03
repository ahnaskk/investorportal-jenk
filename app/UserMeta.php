<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserMeta extends Model
{
    protected $table = 'user_meta';

    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function update_it($userId, $key, $value)
    {
        $meta = self::where('user_id', $userId)->where('key', $key)->first();
        if (! $meta) {
            $meta = self::create([
                'key'   => $key,
                'value' => $value,
                'user_id' => $userId,
            ]);
        } elseif ($meta and $meta->value !== $value) {
            $meta->update(['value' => $value]);
        }

        return $meta;
    }

    public static function find_it($userId, $key, $default = '')
    {
        $meta = self::where('user_id', $userId)->where('key', $key)->first();
        if (! $meta) {
            $meta = self::create([
                'key' => $key,
                'value' => $default,
                'user_id' => $userId,
            ]);
        }

        return $meta->value;
    }

    public static function findObject($userId, $key)
    {
        return self::where('user_id', $userId)->where('key', $key)->first();
    }
}
