<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\AttendanceReminderNotification;
use Carbon\Carbon;

class SendAttendanceReminders extends Command
{
    protected $signature = 'attendance:send-reminders';
    protected $description = 'Send push notifications to remind employees about check-in/out';

    public function handle()
    {
        $settings = SystemSetting::first();
        if (!$settings) return;

        $now = Carbon::now();
        $currentTime = $now->format('H:i');

        // Parse reminder time (15 minutes before)
        $jamMasuk = Carbon::parse($settings->jam_masuk);
        $jamPulang = Carbon::parse($settings->jam_pulang);

        $reminderMasukTime = $jamMasuk->copy()->subMinutes(15)->format('H:i');
        $reminderPulangTime = $jamPulang->copy()->subMinutes(15)->format('H:i');

        $users = User::role('karyawan')->whereHas('pushSubscriptions')->get();

        if ($currentTime === $reminderMasukTime) {
            foreach ($users as $user) {
                $user->notify(new AttendanceReminderNotification('masuk', $jamMasuk->format('H:i')));
            }
            $this->info('Sent check-in reminders.');
        } elseif ($currentTime === $reminderPulangTime) {
            foreach ($users as $user) {
                $user->notify(new AttendanceReminderNotification('pulang', $jamPulang->format('H:i')));
            }
            $this->info('Sent check-out reminders.');
        } else {
            $this->info('No reminders to send at this time.');
        }
    }
}
