<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Meeting;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use App\Notifications\MeetingScheduledNotification;
use Stripe\StripeClient;
use App\Notifications\MeetingPaymentNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Firebase\JWT\JWT;

class ZoomController extends Controller
{

    protected $client_id;
    protected $client_secret;
    protected $redirect_url;
    protected $auth_url;
    protected $token_url;
    protected $api_url;
    protected $token_secret;
    protected $token_verify;

    protected $stripe;
    private $conn;

    public function __construct()
    {
        $client_id = config('services.zoom.client_id');
        $client_secret = config('services.zoom.client_secret');
        $redirect_url = config('services.zoom.redirect_url');
        $auth_url = config('services.zoom.auth_url');
        $token_url = config('services.zoom.token_url');
        $api_url = config('services.zoom.api_url');
        $token_secret = config('services.zoom.token_secret');
        $token_verify = config('services.zoom.token_verify');

        $stripeSecret = config('services.stripe.secret');
        if (empty($stripeSecret)) {
            Log::error('Stripe secret key is not set in config/services.php');
        } else {
            $this->stripe = new StripeClient($stripeSecret);
        }

        $this->conn = DB::connection();
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

        $authUser = Auth::user();

        if(!$authUser) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        $token = hash("md5", uniqid());
        $id = $this->conn->table('pre_schedules')->insertGetId([
            'token' => $token,
            'matchmaker_id' => $request->matchmaker_id,
            'client_ids' => json_encode($request->matchmaker_id),
            'start_time' => $request->start_time,
            'duration' => $request->duration,
        ]);

        // $this->conn->insert('pre_schedules', [
        //         'matchmaker_id' => $request->matchmaker_id,
        //         'client_ids' => json_encode($request->matchmaker_id),
        //         'start_time' => $request->start_time,
        //         'duration' => $request->duration,
        //     ]
        // );

        // $id = $this->conn->lastInsertId();
        // $token = hash("crc32b", $id);

        // $this->conn->table('pre_schedules')->update(['token' => $token])->where(['id' => $id]);

        // $this->conn->update('pre_schedules', ['token' => $token], ['id' => $id]);

        if($authUser->role === 'client') {
            
            $paymentLink = $this->stripe->paymentLinks->create([
                'line_items' => [[
                    'price' => 'price_1Q6dsqFZef913bMWGk4etNyd', // Use the specific Price ID
                    'quantity' => 1,
                ]],
                'after_completion' => [
                    'type' => 'redirect',
                    'redirect' => ['url' => route($this->redirect_url, ['t' => $token])],
                ],
                'metadata' => [
                    't' => $token,
                ],
            ]);

        }
        

    }

    public function createMeeting(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'matchmaker_id' => 'required|exists:users,id',
            'client_ids' => 'required|array|min:1|max:2',
            'start_time' => 'required|date',
            'duration' => 'required|in:15,30,60,120',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for createMeeting', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Retrieve authenticated user
        $authUser = Auth::user();

        if (!$authUser) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please log in.',
            ], 401);
        }

        // Prepare Zoom meeting data
        $roomTitle = "Meeting room";
        $roomDuration = $request->duration;
        $roomTiming = Carbon::parse($request->start_time);

        // Generate meeting details
        $data = [
            'title' => $roomTitle,
            'date' => $roomTiming->toISOString(),
            'duration' => $roomDuration,
            'password' => rand(10000, 99999), // Or use a more secure way to generate passwords
            'created_id' => $authUser->id,
            'api_type' => 'global',
            'host_video' => 'enable',
            'join_before_host' => 'enable',
            'client_video' => 'enable',
            'description' => 'This is my first ' . $roomTitle . '.',
            'timezone' => config('app.timezone'),
        ];

        $accessToken = $this->getAccessToken();

        // Create Zoom meeting
        $response = $this->createAMeeting($data, $accessToken);


        if ($response && isset($response->id)) {
            // Save meeting information to the database
            $meetingData = [
                'meeting_topic' => $response->topic,
                'meeting_start_url' => $response->start_url,
                'meeting_join_url' => $response->join_url,
                'google_meet_id' => $response->id,
                'google_meet_link' => $response->join_url,
                'meeting_response' => json_encode($response, JSON_PRETTY_PRINT),
                'google_meet_password' => $response->password,
                'meeting_id' => $response->id,
                'meeting_password' => $data['password'],
                'meeting_start_time' => $roomTiming,
                'meeting_duration' => $roomDuration,
                'created_by' => $authUser->id,
                'matchmaker_id' => $request->matchmaker_id,
                'start_time' => $roomTiming,
                'duration' => $roomDuration,
                'client_id' => $authUser->id
            ];

            if ($authUser->role === 'client') {

                // Save meeting record to your database (assuming a Meeting model exists)
                Meeting::create($meetingData);

                $stripeSecret = config('services.stripe.secret');

                if (empty($stripeSecret)) {
                    Log::error('Stripe secret key is not set in config/services.php');
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment configuration error. Please try again later.',
                    ], 500);
                }

                $stripe = new StripeClient($stripeSecret);
            }
            // Determine amount and payment flow based on user role
            if ($authUser->role === 'client') {
                // Client is scheduling: require payment
                try {
                    $paymentLink = $stripe->paymentLinks->create([
                        'line_items' => [[
                            'price' => 'price_1Q6dsqFZef913bMWGk4etNyd', // Use the specific Price ID
                            'quantity' => 1,
                        ]],
                        'after_completion' => [
                            'type' => 'redirect',
                            'redirect' => ['url' => route('zoom/create-meeting', ['meeting_id' => $response->id])],
                        ],
                        'metadata' => [
                            'meeting_id' => $response->id,
                        ],
                    ]);

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'start_url' => $response->start_url,
                            'join_url' => $response->join_url,
                            'payment_link' => $paymentLink->url,
                        ],
                        'message' => 'Meeting created successfully. Redirecting to payment...',
                    ], 201);
                } catch (\Exception $e) {
                    Log::error('Error creating Stripe payment link', ['error' => $e->getMessage()]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Error creating payment link',
                    ], 500);
                }
            } else {
                // For matchmaker: create a meeting entry for each client
                $clients = User::whereIn('id', $request->client_ids)->get();
                foreach ($clients as $client) {
                    $clientMeetingData = array_merge($meetingData, [
                        'client_id' => $client->id,
                        'status' => 'confirmed',
                    ]);

                    // Save meeting for each client
                    $newMeeting = Meeting::create($clientMeetingData);
                    // Notification::send($client, new MeetingScheduledNotification($newMeeting));
                }

                // Notify the matchmaker
                // $authUser->notify(new MeetingScheduledNotification($meetingData));
                return response()->json([
                    'success' => true,
                    'data' => [
                        'start_url' => $response->start_url,
                        'join_url' => $response->join_url,
                    ],
                    'message' => 'Meeting scheduled successfully for all clients.',
                ], 200);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create meeting on Zoom.',
            ], 500);
        }
    }

    protected function createAMeeting($data, $accessToken)
    {
        $request_url = 'https://api.zoom.us/v2/users/me/meetings';
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ];

        $postFields = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response);
    }

}