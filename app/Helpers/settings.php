<?php

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        $settings = Cache::remember('settings', 3600, function () {
            return \App\Models\Setting::pluck('value', 'key')->toArray();
        });

        if (array_key_exists($key, $settings)) {
            $value = $settings[$key];

            // Auto-convert JSON
            if (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
                return json_decode($value, true) ?: $value;
            }

            return $value;
        }

        return $default;
    }
}
