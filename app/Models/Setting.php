<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'description',
    ];

    public static function getVal(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    public static function setVal(string $key, $value, ?string $group = null)
    {
        $setting = self::where('key', $key)->first();
        if ($setting) {
            $setting->update(['value' => self::serializeValue($value)]);
            return $setting;
        }

        $type = gettype($value);
        if ($type === 'integer') {
            $dbType = 'integer';
        } elseif ($type === 'boolean') {
            $dbType = 'boolean';
        } elseif ($type === 'array') {
            $dbType = 'json';
        } else {
            $dbType = 'string';
        }

        return self::create([
            'group' => $group ?? 'system',
            'key' => $key,
            'value' => self::serializeValue($value),
            'type' => $dbType,
        ]);
    }

    protected static function castValue($value, string $type)
    {
        if (is_null($value)) {
            return null;
        }

        return match ($type) {
            'integer', 'int' => (int) $value,
            'float', 'double' => (float) $value,
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => json_decode($value, true),
            default => (string) $value,
        };
    }

    protected static function serializeValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return (string) $value;
    }
}
