<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::get()->groupBy('category');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $allowedKeys = SystemSetting::pluck('setting_key')->toArray();
        $updated     = 0;

        foreach ($request->except('_token', '_method', 'apply_capacity_to_existing') as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                $old = SystemSetting::getValue($key);
                SystemSetting::setValue($key, $value);

                AuditLog::record('update_setting', 'system_settings', null, [$key => $old], [$key => $value]);
                $updated++;
            }
        }

        if ($request->boolean('apply_capacity_to_existing')) {
            $newCapacity = SystemSetting::getValue('box_default_capacity');
            \App\Models\FolderBox::query()->update(['capacity' => $newCapacity]);
            AuditLog::record('bulk_update_box_capacity', 'folder_boxes', null, [], ['capacity' => $newCapacity]);
        }

        Cache::flush();

        return redirect()->route('admin.settings.index')->with('success', "{$updated} setting(s) updated.");
    }

    public function preferences()
    {
        $user = auth()->user();
        return view('admin.settings.preferences', compact('user'));
    }

    public function updatePreferences(Request $request)
    {
        $data = $request->validate([
            'page_size'              => 'nullable|in:10,25,50,100',
            'date_format'            => 'nullable|in:MM/DD/YYYY,DD/MM/YYYY,YYYY-MM-DD',
            'auto_fill_archivist'    => 'nullable|boolean',
            'auto_fill_date'         => 'nullable|boolean',
            'default_search_type'    => 'nullable|string',
        ]);

        // ✅ Force boolean keys to always be present (default false when unchecked)
        $data['auto_fill_archivist'] = $request->boolean('auto_fill_archivist');
        $data['auto_fill_date']      = $request->boolean('auto_fill_date');

        $user = auth()->user();
        foreach ($data as $key => $value) {
            $user->setPreference($key, $value);
        }

        return back()->with('success', 'Preferences updated.');
    }
}
