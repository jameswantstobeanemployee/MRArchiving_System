<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SystemSetting;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\FolderBox;
use App\Models\ExternalDrive;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@hospital.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Staff user
        User::create([
            'name' => 'Staff User',
            'email' => 'staff@hospital.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'is_active' => true,
        ]);

        // System Settings
        $settings = [
            // Storage
            ['setting_key' => 'box_default_capacity', 'setting_value' => '50', 'setting_type' => 'integer', 'category' => 'storage', 'description' => 'Default number of charts per box', 'is_editable_by_staff' => false],
            ['setting_key' => 'box_warning_threshold', 'setting_value' => '80', 'setting_type' => 'integer', 'category' => 'storage', 'description' => 'Warning threshold percentage', 'is_editable_by_staff' => false],
            ['setting_key' => 'box_block_threshold', 'setting_value' => '95', 'setting_type' => 'integer', 'category' => 'storage', 'description' => 'Block threshold percentage', 'is_editable_by_staff' => false],
            ['setting_key' => 'max_file_size_mb', 'setting_value' => '100', 'setting_type' => 'integer', 'category' => 'storage', 'description' => 'Max file upload size in MB', 'is_editable_by_staff' => false],
            ['setting_key' => 'allowed_file_types', 'setting_value' => 'pdf,jpg,png,tiff', 'setting_type' => 'string', 'category' => 'storage', 'description' => 'Allowed file types for upload', 'is_editable_by_staff' => false],
            ['setting_key' => 'drive_warning_threshold', 'setting_value' => '80', 'setting_type' => 'integer', 'category' => 'storage', 'description' => 'Drive warning threshold %', 'is_editable_by_staff' => false],
            ['setting_key' => 'drive_critical_threshold', 'setting_value' => '90', 'setting_type' => 'integer', 'category' => 'storage', 'description' => 'Drive critical threshold %', 'is_editable_by_staff' => false],
            ['setting_key' => 'drive_block_threshold', 'setting_value' => '95', 'setting_type' => 'integer', 'category' => 'storage', 'description' => 'Drive block threshold %', 'is_editable_by_staff' => false],
            // Retention
            ['setting_key' => 'retention_available_periods', 'setting_value' => '5,10,15,20,permanent,custom', 'setting_type' => 'string', 'category' => 'retention', 'description' => 'Available retention periods', 'is_editable_by_staff' => false],
            ['setting_key' => 'retention_default_period', 'setting_value' => '10', 'setting_type' => 'integer', 'category' => 'retention', 'description' => 'Default retention in years', 'is_editable_by_staff' => false],
            ['setting_key' => 'retention_calculate_from', 'setting_value' => 'discharge_date', 'setting_type' => 'string', 'category' => 'retention', 'description' => 'Calculate retention from', 'is_editable_by_staff' => false],
            // Checkout
            ['setting_key' => 'checkout_default_loan_days', 'setting_value' => '14', 'setting_type' => 'integer', 'category' => 'checkout', 'description' => 'Default loan period in days', 'is_editable_by_staff' => false],
            ['setting_key' => 'checkout_max_loan_days', 'setting_value' => '30', 'setting_type' => 'integer', 'category' => 'checkout', 'description' => 'Maximum loan period in days', 'is_editable_by_staff' => false],
            ['setting_key' => 'checkout_max_extensions', 'setting_value' => '2', 'setting_type' => 'integer', 'category' => 'checkout', 'description' => 'Maximum number of extensions', 'is_editable_by_staff' => false],
            // Notifications
            ['setting_key' => 'notification_daily_digest_time', 'setting_value' => '08:00', 'setting_type' => 'string', 'category' => 'notifications', 'description' => 'Daily digest email time', 'is_editable_by_staff' => false],
            ['setting_key' => 'notification_overdue_alert', 'setting_value' => '1', 'setting_type' => 'boolean', 'category' => 'notifications', 'description' => 'Send overdue alerts', 'is_editable_by_staff' => false],
            ['setting_key' => 'notification_storage_critical', 'setting_value' => '1', 'setting_type' => 'boolean', 'category' => 'notifications', 'description' => 'Send storage critical alerts', 'is_editable_by_staff' => false],
            // Security
            ['setting_key' => 'security_session_timeout', 'setting_value' => '60', 'setting_type' => 'integer', 'category' => 'security', 'description' => 'Session timeout in minutes', 'is_editable_by_staff' => false],
            ['setting_key' => 'security_max_login_attempts', 'setting_value' => '5', 'setting_type' => 'integer', 'category' => 'security', 'description' => 'Max login attempts', 'is_editable_by_staff' => false],
        ];

        foreach ($settings as $setting) {
            SystemSetting::create($setting);
        }

        // Sample room/shelf/box hierarchy
        $room = Room::create([
            'name' => 'Archive Room 1',
            'code' => 'AR-001',
            'building' => 'Main Building',
            'floor' => '1st Floor',
            'is_active' => true,
        ]);

        for ($s = 1; $s <= 3; $s++) {
            $shelf = Shelf::create([
                'room_id' => $room->id,
                'name' => "Shelf {$s}",
                'code' => "AR-001-S{$s}",
                'section' => "Section {$s}",
                'is_active' => true,
            ]);

            for ($b = 1; $b <= 5; $b++) {
                FolderBox::create([
                    'shelf_id' => $shelf->id,
                    'box_number' => str_pad((($s - 1) * 5) + $b, 3, '0', STR_PAD_LEFT),
                    'box_code' => "AR-001-S{$s}-B{$b}",
                    'capacity' => 50,
                    'is_active' => true,
                ]);
            }
        }

        // Sample external drive
        ExternalDrive::create([
            'name' => 'Primary Archive Drive',
            'drive_path' => 'D:\\',
            'total_space' => 2 * 1024 * 1024 * 1024 * 1024, // 2TB
            'used_space' => 0,
            'is_primary' => true,
            'status' => 'active',
        ]);
    }
}
