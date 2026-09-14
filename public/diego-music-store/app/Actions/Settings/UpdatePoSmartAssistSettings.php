<?php

namespace App\Actions\Settings;

use App\Models\PoSmartAssistSetting;

class UpdatePoSmartAssistSettings
{
    /**
     * Execute saving or updating PO Smart Assist settings.
     *
     * @param  array  $data
     * @return PoSmartAssistSetting
     */
    public function execute(array $data): PoSmartAssistSetting
    {
        $setting = PoSmartAssistSetting::first();

        if (! $setting) {
            $setting = new PoSmartAssistSetting();
        }

        $setting->fill([
            'is_enabled' => (bool) ($data['is_enabled'] ?? true),
            'min_buffer_percentage' => (int) ($data['min_buffer_percentage'] ?? 20),
            'min_buffer_nominal' => (int) ($data['min_buffer_nominal'] ?? 5000000),
            'include_pending_pos' => (bool) ($data['include_pending_pos'] ?? true),
            'include_sales_projection' => (bool) ($data['include_sales_projection'] ?? true),
            'warning_threshold_days' => (int) ($data['warning_threshold_days'] ?? 30),
        ]);

        $setting->save();

        return $setting;
    }
}
