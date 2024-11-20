<?php

namespace App\Console\Commands;
use App\Models\BookMeeting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Illuminate\Console\Command;

class CheckUnconfirmedMeetingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meetings:check-unconfirmed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $now = Carbon::now();
        $eightHoursFromNow = $now->copy()->addHours(8);
        $oneHourFromNow = $now->copy()->addHours(1);
        $meetings = BookMeeting::where(function ($query) use ($oneHourFromNow, $eightHoursFromNow) {
            $query->where(DB::raw("CONCAT(meeting_schedule_date, ' ', meeting_start_time)"), '>=', $oneHourFromNow->toDateTimeString())
                ->where(DB::raw("CONCAT(meeting_schedule_date, ' ', meeting_start_time)"), '<=', $eightHoursFromNow->toDateTimeString());
        })
        ->where('meeting_confirm', 0)
        ->where('email_sent', 0)
        ->get();
        foreach ($meetings as $meeting) {
            $clientEmail = getemail($meeting->client_id);
            $clientName = getname($meeting->client_id);
            $advisorName = getname($meeting->advisor_id);
            $startTime = Carbon::parse($meeting->start_time)->format('Y-m-d h:i A');
            Mail::to($clientEmail)->send(new \App\Mail\MeetingChangeRequest($meeting->id, $advisorName, $clientName, $meeting->meeting_schedule_date, $meeting->meeting_start_time));
            $meeting->email_sent = 1;
            $meeting->save();
        }
    }
}
