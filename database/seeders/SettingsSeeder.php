<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        Setting::updateOrCreate(
            ['key' => 'site.title'],
            ['value' => 'TCAM Solution', 'group' => 'site', 'description' => 'Site title']
        );
        Setting::updateOrCreate(
            ['key' => 'site.description'],
            ['value' => 'Admin panel for TCAM Solution', 'group' => 'site', 'description' => 'Site description']
        );
        Setting::updateOrCreate(
            ['key' => 'notifications.contact_email'],
            ['value' => 'support@example.com', 'group' => 'notifications', 'description' => 'Where to send contact form notifications']
        );
    }
}
