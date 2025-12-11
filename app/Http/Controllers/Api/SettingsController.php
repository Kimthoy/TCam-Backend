<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * GET /api/admin/settings
     *
     * Returns:
     * {
     *   "data": {
     *     "site.title": "My Website",
     *     "site.description": "...",
     *     "notifications.email": "support@example.com"
     *   }
     * }
     */
    public function index()
    {
        $settings = Setting::query()->get(['key', 'value']);

        // Convert to a simple "key" => "value" map
        $map = $settings->mapWithKeys(function ($s) {

            // Decode if JSON string was stored
            $value = $s->value;

            if (is_string($value)) {
                $decoded = json_decode($value, true);
                $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
            }

            return [$s->key => $value];
        });

        return response()->json(['data' => $map]);
    }

    /**
     * PUT /api/admin/settings
     *
     * Accepts:
     * 1. Single: { "key": "site.title", "value": "New Title" }
     * 2. Batch: [{ key, value }, { key, value }]
     * 3. FormData (key=value pairs or files)
     */
    public function update(Request $request)
    {
        $payload = $request->all();

        // --- CASE 1: Batch array ------------------------------------
        if (is_array($payload) && array_is_list($payload)) {
            foreach ($payload as $item) {
                $this->saveSettingItem($request, $item);
            }
            return response()->json(['message' => 'Settings updated (batch).']);
        }

        // --- CASE 2: Single {key, value} ----------------------------
        if (isset($payload['key']) && array_key_exists('value', $payload)) {
            $this->saveSettingItem($request, $payload);
            return response()->json(['message' => 'Setting updated.']);
        }

        // --- CASE 3: Raw FormData key => value pairs ----------------
        foreach ($payload as $key => $value) {
            $this->saveSettingByKey($request, $key, $value);
        }

        return response()->json(['message' => 'Settings updated.']);
    }

    /**
     * Save one setting from { key, value }
     */
    protected function saveSettingItem(Request $request, array $item)
    {
        $key = $item['key'] ?? null;
        $value = $item['value'] ?? null;
        $group = $item['group'] ?? 'general';
        $description = $item['description'] ?? null;

        if (!$key) {
            return;
        }

        // File upload: FormData must include field name = key
        if ($request->hasFile($key)) {
            $file = $request->file($key);
            $path = $file->store('settings', 'public');
            $value = Storage::url($path);
        }

        Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'description' => $description,
            ]
        );
    }

    /**
     * Save one setting from raw key => value (FormData)
     */
    protected function saveSettingByKey(Request $request, string $key, $value)
    {
        if ($request->hasFile($key)) {
            $path = $request->file($key)->store('settings', 'public');
            $value = Storage::url($path);
        }

        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Optional:
     * POST /api/admin/settings/refresh-cache
     */
    public function refreshCache()
    {
        // Your custom caching system here, if using one
        // e.g., app(SettingsManager::class)->refresh();

        return response()->json(['message' => 'Settings cache refreshed']);
    }
}
