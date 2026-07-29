<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class AttendanceReminderNotification extends Notification
{
    use Queueable;

    private $type;
    private $time;

    public function __construct($type, $time)
    {
        $this->type = $type;
        $this->time = $time;
    }

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $title = $this->type === 'masuk' ? 'Pengingat Jam Masuk' : 'Pengingat Jam Pulang';
        $body = $this->type === 'masuk' 
            ? "Halo {$notifiable->name}, jam masuk adalah {$this->time}. Jangan lupa untuk check-in!" 
            : "Halo {$notifiable->name}, jam pulang adalah {$this->time}. Jangan lupa untuk check-out!";

        return (new WebPushMessage)
            ->title($title)
            ->icon('/logo.png')
            ->body($body)
            ->action('Buka Aplikasi', '/')
            ->options(['TTL' => 1000]);
    }
}
