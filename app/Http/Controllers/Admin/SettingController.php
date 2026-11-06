<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        $this->ensureSuperAdmin();

        $settings = SystemSetting::all();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $this->ensureSuperAdmin();

        $settingsData = $request->input('settings', []);
        $oldValues = SystemSetting::all()->pluck('value', 'key')->toArray();

        foreach ($settingsData as $key => $value) {
            SystemSetting::where('key', $key)->update(['value' => $value]);
        }

        $newValues = SystemSetting::all()->pluck('value', 'key')->toArray();

        AuditLog::record('CONFIG_UPDATED', null, "Konfigurasi sistem diperbarui oleh Super Admin.", $oldValues, $newValues);

        return back()->with('success', 'Konfigurasi sistem berhasil diperbarui.');
    }

    private function ensureSuperAdmin(): void
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Akses ditolak.');
        }
    }
}
