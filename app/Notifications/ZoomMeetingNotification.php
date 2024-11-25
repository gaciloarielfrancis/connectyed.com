<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class ZoomMeetingNotification extends Notification
{
    use Queueable;

    protected $meeting;

    public function __construct($meeting) {
        $this->meeting = $meeting;
    }

    public function via(object $notifiable): array {
        return ['mail'];
    }

    public function toMail($notifiable) {
        $datetime = date_create($this->meeting['start_time']);
        return (new MailMessage)
                    ->subject('Meeting Scheduled Successfully via Connectyed')
                    ->greeting('Good day!')
                    ->line('I hope this message finds you well. This is a friendly reminder that you have a scheduled meeting.')
                    ->line('Agenda: '.$this->meeting['agenda'])
                    ->line('Reason: '.$this->meeting['meeting_response'])
                    ->line('Schedule/s:')
                    ->when(count($this->meeting['schedules']) > 0, function (MailMessage $mail) { 
                        foreach ($this->meeting['schedules'] as $schedule) {
                            $from = date_format(date_create($schedule->date . ' ' .$schedule->from), 'F j, Y g:ia');
                            $to = date_format(date_create($schedule->date . ' ' .$schedule->to), 'g:ia');
                            $mail->line($from . ' - ' . $to);
                        }
                        return $mail;
                    }
                    )
                    ->line('Timezone: '.$this->meeting['timezone'])
                    ->action('Click to Join Meeting', $this->meeting['meet_link'])
                    ->line("Please ensure that you're available and prepared for the meeting")
                    ->line('Thank you for using our application!');
    }

    public function toArray($notifiable) {
        return [];
    }
}
