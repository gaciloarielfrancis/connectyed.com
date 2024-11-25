<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Meeting;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ZoomMeetingNotification;
use Stripe\StripeClient;
use App\Notifications\MeetingPaymentNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Firebase\JWT\JWT;

class ZoomController extends Controller {
    
    protected $stripe;
    private $conn;

    public function __construct() {
        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret)) {
            Log::error('Stripe secret key is not set in config/services.php');
        } else {
            $this->stripe = new StripeClient($stripeSecret);
        }

        $this->conn = DB::connection();
    }

    private function ZoomConfig() {
        return [
            'ACCOUNT_ID' => config('services.zoom.account_id'),
            'CLIENT_ID' => config('services.zoom.client_id'),
            'CLIENT_SECRET' => config('services.zoom.client_secret'),
            'REDIRECT_URI' => config('services.zoom.redirect_url'),
            'AUTH_URL' => config('services.zoom.auth_url'),
            'TOKEN_URL' => config('services.zoom.token_url'),
            'API_URL' => config('services.zoom.api_url'),
            'TOKEN_SECRET' => config('services.zoom.token_secret'),
            'TOKEN_VERIFY' => config('services.zoom.token_verify'),
        ];
    }

    private function auth() {
        $authUser = Auth::user();

        if(!$authUser) {
            return response()->json([
                'verified' => false,
                'success' => false,
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        $authUser->verified = true;
        return $authUser;
    }

    private function getAccessToken() {
        $url = $this->ZoomConfig()['TOKEN_URL'].'?grant_type=account_credentials&account_id='.$this->ZoomConfig()['ACCOUNT_ID'];

        $headers = [
            'Authorization: Basic ' . base64_encode($this->ZoomConfig()['CLIENT_ID'].':'.$this->ZoomConfig()['CLIENT_SECRET']),
            'Content-Type: application/x-www-form-urlencoded'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }

    public function proceedPayment(int $id) {
        $confirm_token = hash('md5', uniqid());

        $paymentLink = $this->stripe->paymentLinks->create([
            'line_items' => [[
                'price' => 'price_1Q6dsqFZef913bMWGk4etNyd', // Use the specific Price ID
                'quantity' => 1,
            ]],
            'after_completion' => [
                'type' => 'redirect',
                'redirect' => [ 'url' => env('APP_HOST') . '/api/zoom/confirm-payment/'.$confirm_token ],
            ]
        ]);

        $this->conn->select('UPDATE meetings SET confirm_token=? WHERE id=?', [$confirm_token, $id]);

        return Redirect::to($paymentLink->url);
    }

    public function confirmPayment($token) {
        $meeting = $this->conn->table('meetings')->where(['confirm_token' => $token])->first();
        $client = $this->conn->table('users')->where(['id' => $meeting->client_id])->first();
        $matchmaker = $this->conn->table('users')->where(['id' => $meeting->matchmaker_id])->first();

        $response = $this->createMeeting($meeting, $client, $matchmaker);
        if(!$response) {
            return response()->json([
                'success' => false,
                'message' => 'Creating meeting schedule was failed, please try again later.',
            ], 201);
        }

        $this->conn->select('UPDATE meetings SET google_meet_id=?, google_meet_link=?, google_meet_password=? WHERE confirm_token=?', 
        [$response->id , $response->join_url, $response->password, $token]);

        $meeting_data = [
            "agenda" => $response->agenda,
            "schedules" => json_decode($meeting->schedules),
            "start_time" => $meeting->start_time,
            "end_time" => $meeting->end_time,
            "timezone" => $meeting->timezone,
            "duration" => $meeting->duration,
            "meeting_response" => $meeting->meeting_response,
            "start_url" => $response->start_url,
            "join_url" => $response->join_url,
            "meet_link" => $response->join_url,
            "meeting_password" => $response->password
        ];

        Notification::route('mail', $client->email)->notify(new ZoomMeetingNotification($meeting_data));
        Notification::route('mail', $matchmaker->email)->notify(new ZoomMeetingNotification($meeting_data));

        return Redirect::to(env('APP_HOST').'/client/communication');
    }

    public function payForMeeting(Request $request) {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'matchmaker_id' => 'required|exists:users,id',
            'client_ids' => 'required|array|min:1|max:2',
            'start_time' => 'required|date',
            'duration' => 'required|in:15,30,60,120,1440',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for createMeeting', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $auth = $this->auth();
        if(!$auth->verified) return $auth;

        $token = $this->createPreSchedule($request);
        
        $paymentLink = $this->stripe->paymentLinks->create([
            'line_items' => [[
                'price' => 'price_1Q6dsqFZef913bMWGk4etNyd', // Use the specific Price ID
                'quantity' => 1,
            ]],
            'after_completion' => [
                'type' => 'redirect',
                'redirect' => [ 'url' => env('APP_HOST') . '/api/zoom/schedule-meeting/'.$token ],
            ],
            'metadata' => [
                't' => $token,
            ],
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'start_url' => '',
                'join_url' => '',
                'payment_link' => $paymentLink->url,
            ],
            'message' => 'Meeting created successfully. Redirecting to payment...',
        ], 201);
    }

    private function createPreSchedule(Request $request) {
        $auth = $this->auth();
        $token = hash("md5", uniqid());
        $id = $this->conn->table('pre_schedules')->insertGetId([
            'token' => $token,
            'matchmaker_id' => $request->matchmaker_id,
            'client_id' => $request->client_id,
            'timezone' => $request->timezone,
            'schedules' => json_encode($request->schedules),
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'duration' => $request->duration,
            'meeting_response' => $request->meeting_response,
            'scheduled_by' => $auth->id
        ]);
        return $token;
    }

    public function requestAMeeting(Request $request) {
        $auth = $this->auth();
        if(!$auth->verified) return $auth;
        
        $token = $this->createPreSchedule($request);
        $this->scheduleMeeting($token, false, true);

        return response()->json([
            'success' => true,
            'message' => 'Meeting request was successfully sent.',
        ], 201);
    }

    public function createAMeeting(Request $request) {
        $auth = $this->auth();
        if(!$auth->verified) return $auth;

        $token = $this->createPreSchedule($request);
        $this->scheduleMeeting($token, true);

        return response()->json([
            'success' => true,
            'payment_link' => env('APP_HOST').'/client/communication',
            'message' => 'Meeting created successfully.',
        ], 201);
    }

    public function scheduleMeeting($token, $is_paid = false, $is_matchmaker = false) {
        $schedule = $this->conn->table('pre_schedules')->where(['token' => $token])->first();
        $client = $this->conn->table('users')->where(['id' => $schedule->client_id])->first();
        $matchmaker = $this->conn->table('users')->where(['id' => $schedule->matchmaker_id])->first();

        $response = false;

        if($is_paid) {
            $response = $this->createMeeting($schedule, $client, $matchmaker);
            if(!$response) {
                return response()->json([
                    'success' => false,
                    'message' => 'Creating meeting schedule was failed, please try again later.',
                ], 201);
            }
        }

        $this->conn->table('meetings')->insert([
            'client_id' => $client->id,
            'matchmaker_id' => $matchmaker->id,
            'google_meet_id' => $is_paid ? $response->id : '',
            'google_meet_link' => $is_paid ? $response->join_url : '',
            'google_meet_password' => $is_paid ? $response->password : '',
            'meeting_response' => $schedule->meeting_response,
            'schedules' => $schedule->schedules,
            'timezone' => $schedule->timezone,
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'duration' => $schedule->duration,
            'status' => $is_paid ? 'success' : 'waiting',
            'scheduled_by' => $schedule->scheduled_by
        ]);

        if($is_paid) {
            $meeting_data = [
                "agenda" => $response->agenda,
                "schedules" => json_decode($schedule->schedules),
                "start_time" => $schedule->start_time,
                "end_time" => $schedule->end_time,
                "timezone" => $schedule->timezone,
                "duration" => $schedule->duration,
                "meeting_response" => $schedule->meeting_response,
                "start_url" => $response->start_url,
                "join_url" => $response->join_url,
                "meet_link" => $response->join_url,
                "meeting_password" => $response->password
            ];
    
            Notification::route('mail', $client->email)->notify(new ZoomMeetingNotification($meeting_data));
            Notification::route('mail', $matchmaker->email)->notify(new ZoomMeetingNotification($meeting_data));
        }

        if(!$is_paid && !$is_matchmaker) {
            echo "Please wait... it will redirect to your account.";
            return Redirect::to(env('APP_HOST').'/client/communication');
        }
        
    }

    public function createMeeting($data, $client, $matchmaker) {
        $auth = $this->getAccessToken();
        $token = $auth->access_token;

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->ZoomConfig()['API_URL']."/users/me/meetings",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode([
                'agenda' => 'Meeting via Connectyed',
                'duration' => $data->duration,
                'password' => strtoupper(hash('crc32b', uniqid())),
                'pre_schedule' => true,
                'recurrence' => [
                    'end_date_time' => $data->end_time,
                    'type' => 1
                ],
                'settings' => [
                    'calendar_type' => 1,
                    'contact_email' => $client->email,
                    'contact_name' => $client->name,
                    'encryption_type' => 'enhanced_encryption',
                    'focus_mode' => false,
                    'global_dial_in_countries' => [
                        'US'
                    ],
                    'host_video' => false,
                    'jbh_time' => 0,
                    'join_before_host' => true,
                    'meeting_authentication' => false,
                    'meeting_invitees' => [
                        [
                            'email' => $client->email
                        ],
                        [
                            'email' => $matchmaker->email
                        ]
                    ],
                    'participant_video' => false,
                    'private_meeting' => false,
                    'registrants_confirmation_email' => true,
                    'registrants_email_notification' => true,
                    'registration_type' => 1,
                    'show_share_button' => true,
                    'waiting_room' => false,
                    'host_save_video_order' => true,
                    'alternative_host_update_polls' => false,
                ],
                'start_time' => $data->start_time,
                'timezone' => $data->timezone,
                'topic' => "Let us connected!",
                'type' => 2
            ]),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer ".$token,
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return false;
        } else {
            return json_decode($response);
        }
    }

    public function getUpcomingMeetings(Request $request) {
        $user = $this->auth();
        if(!$user->verified) return $user;

        if($user->role === 'client') {
            $rows = $this->conn->select(
                'SELECT id, matchmaker_id, client_id, google_meet_id, google_meet_link, schedules, timezone, start_time, end_time, duration FROM meetings WHERE client_id=? AND end_time>? ORDER BY start_time ASC', 
                [ $user->id, now() ]
            );
            foreach($rows as $row) {
                $row->schedules = json_decode($row->schedules);
                $row->matchmaker = $this->conn->select('SELECT name, email from users WHERE id=?', [$row->matchmaker_id])[0];
            }
            return response()->json([
                'success' => true,
                'data' => $rows
            ], 200);
        }else{
            $rows = $this->conn->select(
                'SELECT id, matchmaker_id, client_id, google_meet_id, google_meet_link, schedules, timezone, start_time, end_time, duration FROM meetings WHERE matchmaker_id=? AND end_time>? ORDER BY start_time ASC', 
                [ $user->id, now() ]
            );
            foreach($rows as $row) {
                $row->schedules = json_decode($row->schedules);
                $row->client = $this->conn->select('SELECT name, email from users WHERE id=?', [$row->client_id])[0];
            }
            return response()->json([
                'success' => true,
                'data' => $rows,
            ], 200);
        } 
    }

}