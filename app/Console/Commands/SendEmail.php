<?php

namespace App\Console\Commands;

use App\Models\BookMeeting;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
class SendEmail extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'send:email';

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
    $bookedMeetings = BookMeeting::where('meeting_schedule_date', date('Y-m-d'))->where('mail_status', '0')->get();
    foreach($bookedMeetings as $meeting){
      $meetingStartTime = Carbon::parse($meeting->meeting_start_time);
      $currentTime = Carbon::parse(date('h:i a'));
      if($meetingStartTime > $currentTime){
        $totalDuration = $currentTime->diffInMinutes($meetingStartTime);
        if($totalDuration <= 30){
          $data = [];
          $data['title'] = '30 Minutes Advice';
          $data['password'] = $meeting->meeting_password;
          $data['start_url'] = $meeting->meeting_start_link;
          $data['join_url'] = $meeting->meeting_join_link;
          $data['start_time'] = $meeting->meeting_start_time;
          $data['end_time'] = $meeting->meeting_end_time;
          $data['meeting_date'] = $meeting->meeting_schedule_date;
          $data['client_email'] = false;
          $data['client_email'] = true;
          Mail::to($meeting->email)->send(new \App\Mail\MeetingLink($data));
          $meeting = BookMeeting::find($meeting->id);
          $meeting->mail_status = 1;
          $meeting->save();
          // dd($totalDuration, $meeting->meeting_start_time, date('h:i a'));
        }

      }

    }
    // dd(date('h:i a'));
  }
}
