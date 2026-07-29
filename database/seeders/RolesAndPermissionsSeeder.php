<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $admin      = Role::firstOrCreate(['name' => 'admin',      'guard_name' => 'web']);
        $karyawan   = Role::firstOrCreate(['name' => 'karyawan',   'guard_name' => 'web']);

        // Create default superadmin user
        $user = User::firstOrCreate(
            ['email' => 'superadmin@absensirb.com'],
            [
                'name'     => 'Super Admin',
                'email'    => 'superadmin@absensirb.com',
                'password' => Hash::make('password'),
                'nik'      => 'SA001',
                'jabatan'  => 'System Administrator',
                'status'   => 'aktif',
            ]
        );
        $user->syncRoles([$superadmin]);

        // System settings defaults
        $defaults = [
            ['key' => 'jam_masuk',                'value' => '08:00', 'description' => 'Jam masuk kerja (format HH:MM)'],
            ['key' => 'jam_pulang',               'value' => '17:00', 'description' => 'Jam pulang kerja (format HH:MM)'],
            ['key' => 'toleransi_terlambat_menit','value' => '15',    'description' => 'Toleransi keterlambatan dalam menit'],
            ['key' => 'nama_kantor',              'value' => 'Kantor Pusat', 'description' => 'Nama kantor untuk laporan'],
        ];

        foreach ($defaults as $setting) {
            SystemSetting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
