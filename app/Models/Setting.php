<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    /**
     * Cast JSON values automatically.
     * If `value` column is JSON type, this works perfectly.
     */
    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Shortcut accessor
     * Example: Setting::getValue('site.title');
     */
    public static function getValue(string $key, $default = null)
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    /**
     * Save key/value easily
     * Example: Setting::setValue('site.title', 'My Site');
     */
    public static function setValue(string $key, $value, $group = 'general')
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
            ]
        );
    }
}
