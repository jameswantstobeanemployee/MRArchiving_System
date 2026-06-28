<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExternalDrive;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StorageController extends Controller
{
    public function index()
    {
        $drives = ExternalDrive::orderByDesc('is_primary')->orderBy('name')->get();
        return view('admin.storage.index', compact('drives'));
    }

    public function create()
    {
        return view('admin.storage.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'drive_path' => 'required|string|max:255',
            'is_primary' => 'boolean',
        ]);

        $scanWarning = null;

        DB::transaction(function () use ($data, &$scanWarning) {
            if ($data['is_primary'] ?? false) {
                ExternalDrive::where('is_primary', true)->update(['is_primary' => false]);
            }

            $drive = ExternalDrive::create([
                'name'       => $data['name'],
                'drive_path' => $data['drive_path'],
                'is_primary' => $data['is_primary'] ?? false,
                'status'     => 'active',
            ]);

            AuditLog::record('create_drive', 'external_drives', $drive->id, null, $drive->toArray());

            try {
                $this->scanDrive($drive);
            } catch (\Exception $e) {
                $scanWarning = $e->getMessage();
            }
        });

        if ($scanWarning) {
            return redirect()->route('admin.storage.index')
                ->with('success', 'Drive added successfully.')
                ->with('warning', $scanWarning);
        }

        return redirect()->route('admin.storage.index')->with('success', 'Drive added successfully.');
    }

    public function edit(ExternalDrive $drive)
    {
        return view('admin.storage.edit', compact('drive'));
    }

    public function update(Request $request, ExternalDrive $drive)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'drive_path' => 'required|string|max:255',
            'is_primary' => 'boolean',
            'status'     => 'required|in:active,inactive',
        ]);

        DB::transaction(function () use ($data, $drive, $request) {
            $old = $drive->toArray();

            if (($data['is_primary'] ?? false) && !$drive->is_primary) {
                ExternalDrive::where('is_primary', true)->update(['is_primary' => false]);
            }

            $data['is_primary'] = $request->boolean('is_primary');
            $drive->update($data);

            AuditLog::record('update_drive', 'external_drives', $drive->id, $old, $drive->toArray());
        });

        return redirect()->route('admin.storage.index')->with('success', 'Drive updated.');
    }

    public function scan(ExternalDrive $drive)
    {
        try {
            $this->scanDrive($drive);
            $criticalThreshold = SystemSetting::getValue('drive_critical_threshold', 90);

            if ($drive->used_percentage >= $criticalThreshold) {
                Notification::sendToAdmins(
                    'storage_critical',
                    'Storage Critical',
                    "Drive '{$drive->name}' is {$drive->used_percentage}% full.",
                    'both'
                );
            }

            return back()->with('success', "Drive scanned. Used: {$drive->used_space_formatted} / {$drive->total_space_formatted}");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Scan failed: ' . $e->getMessage()]);
        }
    }

    public function setPrimary(ExternalDrive $drive)
    {
        DB::transaction(function () use ($drive) {
            ExternalDrive::where('is_primary', true)->update(['is_primary' => false]);
            $drive->update(['is_primary' => true]);
            AuditLog::record('set_primary_drive', 'external_drives', $drive->id);
        });

        return back()->with('success', "'{$drive->name}' set as primary drive.");
    }

    public function destroy(ExternalDrive $drive)
    {
        if ($drive->is_primary) {
            return back()->withErrors(['error' => 'Cannot delete the primary drive. Set another drive as primary first.']);
        }

        DB::transaction(function () use ($drive) {
            AuditLog::record('delete_drive', 'external_drives', $drive->id, $drive->toArray(), null);
            $drive->delete();
        });

        return redirect()->route('admin.storage.index')->with('success', "Drive '{$drive->name}' deleted.");
    }

    private function scanDrive(ExternalDrive $drive): void
    {
        $path = $drive->drive_path;

        if (is_dir($path) || is_file($path)) {
            $total     = disk_total_space($path) ?: 0;
            $free      = disk_free_space($path) ?: 0;
            $used      = $total - $free;

            $drive->update([
                'total_space'     => $total,
                'used_space'      => $used,
                'last_scanned_at' => now(),
                'status'          => 'active',
            ]);
        } else {
            // Path not accessible - update status but don't fail
            $drive->update([
                'status'          => 'error',
                'last_scanned_at' => now(),
            ]);
            throw new \Exception("Drive path '{$path}' is not accessible.");
        }
    }
}
