<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MatchmakerClient;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */

    private $conn;

    public function __construct()
    {
        // Apply both 'auth:api' and 'verified' middleware
        $this->middleware(['auth:api', 'verified'])->except(['getPublicClientsByMatchmakerId']);

        $this->conn = DB::connection();
    }

    /**
     * Add a new client under the authenticated matchmaker.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addClients(Request $request)
    {
        try {
            DB::beginTransaction();

            // Debug log: Start of addClients
            Log::info('Attempting to add a new client.', ['request' => $request->all()]);

            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'age' => 'required|integer|min:18|max:100',
                'heightFeet' => 'required|integer|min:1|max:8',
                'heightInches' => 'required|integer|min:0|max:11',
                'bodytype' => 'required|string|max:50',
                'interests' => 'required|string|max:500',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'country' => 'required|string|max:100',
                'maritalStatus' => 'required|string|max:50',
                'children' => 'required|integer|min:0|max:10',
                'religion' => 'required|string|max:100',
                'smoker' => 'required|in:Yes,No',
                'drinker' => 'required|in:None,Occasionally,Often',
                'education' => 'required|string|max:255',
                'jobTitle' => 'required|string|max:255',
                'sports' => 'required|string|max:255',
                'hobbies' => 'required|string|max:255',
                'languages' => 'required|string|max:255',
                'englishLevel' => 'nullable|string|max:100', // Assuming this is optional
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed for adding a client.', ['errors' => $validator->errors()->toArray()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get authenticated matchmaker
            $matchmaker = Auth::user();
            if ($matchmaker->role !== 'matchmaker') {
                Log::warning('Unauthorized attempt to add client by user.', ['user_id' => $matchmaker->id, 'role' => $matchmaker->role]);
                return response()->json([
                    'success' => false,
                    'message' => 'Only matchmakers can add clients'
                ], 403);
            }

            // Handle avatar upload
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('clients/avatars', 'public');
                Log::info('Avatar uploaded successfully.', ['path' => $avatarPath]);
            }

            // Handle additional images
            $additionalImagesPaths = [];
            if ($request->hasFile('additional_images')) {
                foreach ($request->file('additional_images') as $image) {
                    $path = $image->store('clients/additional', 'public');
                    if ($path) {
                        $additionalImagesPaths[] = $path;
                        Log::info('Additional image uploaded successfully.', ['path' => $path]);
                    }
                }
            }

            // Create the client user
            $clientUser = User::create([
                'name' => $request->name,
                'username' => $this->generateUsername($request->name),
                'email' => $request->email,
                'password' => Hash::make(Str::random(12)), // Increased randomness for security
                'role' => 'client',
                'email_verified_at' => now(),
            ]);

            Log::info('Client user created successfully.', ['user_id' => $clientUser->id]);

            // Create MatchmakerClient record
            $matchmakerClient = MatchmakerClient::create([
                'matchmaker_id' => $matchmaker->id,
                'user_id' => $clientUser->id,
                'age' => $request->age,
                'height_feet' => $request->heightFeet,
                'height_inches' => $request->heightInches,
                'body_type' => $request->bodytype,
                'interests' => $request->interests,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'marital_status' => $request->maritalStatus,
                'children' => $request->children,
                'religion' => $request->religion,
                'thumbnail_image' => $avatarPath,
                'additional_images' => json_encode($additionalImagesPaths)
            ]);

            Log::info('MatchmakerClient record created successfully.', ['matchmaker_client_id' => $matchmakerClient->id]);

            // Create Profile record
            $profile = Profile::create([
                'user_id' => $clientUser->id,
                'matchmaker_id' => $matchmaker->id,
                'name' => $request->name,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'age' => $request->age,
                'bodytype' => $request->bodytype,
                'height' => $request->heightFeet,
                'inches' => $request->heightInches,
                'maritalstatus' => $request->maritalStatus,
                'children' => $request->children,
                'religion' => $request->religion,
                'smoker' => $request->smoker === 'Yes',
                'drinker' => $request->drinker,
                'education' => $request->education,
                'jobtitle' => $request->jobTitle,
                'sports' => $request->sports,
                'hobbies' => $request->hobbies,
                'languages' => $request->languages,
                'english_level' => $request->englishLevel, // Changed from english to english_level
                'profile_image1' => $avatarPath,
            ]);

            Log::info('Profile record created successfully.', ['profile_id' => $profile->id]);

            DB::commit();

            Log::info('Client added successfully.', ['client_user_id' => $clientUser->id]);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $clientUser,
                    'profile' => $profile,
                    'matchmaker_client' => $matchmakerClient
                ],
                'message' => 'Client added successfully.'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding client: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? 'Failed to add client: ' . $e->getMessage() : 'Failed to add client.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Generate a unique username from a name.
     *
     * @param string $name
     * @return string
     */
    private function generateUsername($name)
    {
        $baseUsername = Str::slug($name);
        $username = $baseUsername;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Fetch clients for a specific matchmaker.
     *
     * @param int $matchmakerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClientsByMatchmakerId($matchmakerId)
    {
        try {
            $user = Auth::user();
            
            if ($user->role !== 'admin' && $user->id != $matchmakerId) {
                Log::warning('Unauthorized access attempt.', ['user_id' => $user->id, 'requested_matchmaker_id' => $matchmakerId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $clients = MatchmakerClient::where('matchmaker_id', $matchmakerId)
                ->with([
                    'matchmaker:id,name,email',
                    'user:id,name,email',
                    'user.profile' // Include the profile relationship
                ])
                ->get();

            // Transform the image paths
            $clients->transform(function ($client) {
                if ($client->thumbnail_image) {
                    $client->thumbnail_image = Storage::url($client->thumbnail_image);
                }
                if ($client->additional_images) {
                    $images = json_decode($client->additional_images, true);
                    $client->additional_images = array_map(function($image) {
                        return Storage::url($image);
                    }, $images);
                }
                return $client;
            });

            Log::info('Fetched clients successfully.', ['matchmaker_id' => $matchmakerId, 'clients_count' => $clients->count()]);

            return response()->json([
                'success' => true,
                'data' => $clients,
                'message' => 'Clients fetched successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching clients: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? 'Failed to fetch clients: ' . $e->getMessage() : 'Failed to fetch clients',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }


    /**
     * Update an existing client's information.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $clientId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateClient(Request $request, $clientId)
    {
        try {
            DB::beginTransaction();
            
            $matchmaker = Auth::user();
            $client = MatchmakerClient::with(['user', 'user.profile'])->findOrFail($clientId);

            // Validate matchmaker ownership
            if ($client->matchmaker_id != $matchmaker->id) {
                Log::warning('Unauthorized access attempt to update client.', ['user_id' => $matchmaker->id, 'client_id' => $clientId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Validation rules with ignore for email
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('users')->ignore($client->user_id)],
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'age' => 'required|integer|min:18|max:100',
                'heightFeet' => 'required|integer|min:1|max:8',
                'heightInches' => 'required|integer|min:0|max:11',
                'bodytype' => 'required|string|max:50',
                'interests' => 'required|string|max:500',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'country' => 'required|string|max:100',
                'maritalStatus' => 'required|string|max:50',
                'children' => 'required|integer|min:0|max:10',
                'religion' => 'required|string|max:100',
                'smoker' => 'required|in:Yes,No',
                'drinker' => 'required|in:None,Occasionally,Often',
                'education' => 'required|string|max:255',
                'jobTitle' => 'required|string|max:255',
                'sports' => 'required|string|max:255',
                'hobbies' => 'required|string|max:255',
                'languages' => 'required|string|max:255',
                'englishLevel' => 'nullable|string|max:100', // Assuming this is optional
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed for updating a client.', ['errors' => $validator->errors()->toArray()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle file uploads
            $avatarPath = $client->thumbnail_image;
            if ($request->hasFile('avatar')) {
                // Delete old avatar if it exists
                if ($avatarPath) {
                    Storage::delete($avatarPath);
                    Log::info('Old avatar deleted.', ['path' => $avatarPath]);
                }
                $avatarPath = $request->file('avatar')->store('clients/avatars', 'public');
                Log::info('New avatar uploaded successfully.', ['path' => $avatarPath]);
            }

            $additionalImagesPaths = $client->additional_images ? json_decode($client->additional_images, true) : [];
            if ($request->hasFile('additional_images')) {
                foreach ($request->file('additional_images') as $image) {
                    $path = $image->store('clients/additional', 'public');
                    if ($path) {
                        $additionalImagesPaths[] = $path;
                        Log::info('Additional image uploaded successfully.', ['path' => $path]);
                    }
                }
            }

            // Update User
            $client->user->update([
                'name' => $request->name,
                'email' => $request->email
            ]);
            Log::info('Client user updated successfully.', ['user_id' => $client->user_id]);

            // Update MatchmakerClient
            $client->update([
                'age' => $request->age,
                'height_feet' => $request->heightFeet,
                'height_inches' => $request->heightInches,
                'body_type' => $request->bodytype,
                'interests' => $request->interests,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'marital_status' => $request->maritalStatus,
                'children' => $request->children,
                'religion' => $request->religion,
                'thumbnail_image' => $avatarPath,
                'additional_images' => json_encode($additionalImagesPaths)
            ]);
            Log::info('MatchmakerClient record updated successfully.', ['matchmaker_client_id' => $client->id]);

            // Update or Create Profile
            $profile = $client->user->profile;
            if (!$profile) {
                $profile = new Profile();
                $profile->user_id = $client->user_id;
            }

            $profile->fill([
                'matchmaker_id' => $matchmaker->id,
                'name' => $request->name,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'age' => $request->age,
                'bodytype' => $request->bodytype,
                'height' => $request->heightFeet,
                'inches' => $request->heightInches,
                'maritalstatus' => $request->maritalStatus,
                'children' => $request->children,
                'religion' => $request->religion,
                'smoker' => $request->smoker === 'Yes',
                'drinker' => $request->drinker,
                'education' => $request->education,
                'jobtitle' => $request->jobTitle,
                'sports' => $request->sports,
                'hobbies' => $request->hobbies,
                'languages' => $request->languages,
                'english_level' => $request->englishLevel, // Changed from english to english_level
                'profile_image1' => $avatarPath,
            ]);
            $profile->save();
            Log::info('Profile record updated successfully.', ['profile_id' => $profile->id]);

            DB::commit();

            Log::info('Client updated successfully.', ['client_user_id' => $client->user_id]);

            return response()->json([
                'success' => true,
                'message' => 'Client updated successfully',
                'data' => $client->load(['user', 'user.profile'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating client: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? 'Failed to update client: ' . $e->getMessage() : 'Failed to update client.',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function getAllClientsBasicInfo(Request $request) {
        $user = Auth::user();
            
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $clients = $this->conn->select('SELECT id, name, email FROM users WHERE role="client" AND email_verified_at IS NOT NULL ORDER BY name ASC');
        
        return response()->json([
            'success' => true,
            'data' => $clients,
            'message' => 'Clients fetched successfully'
        ]);
    }

    /**
     * Get all clients for the current user based on their role.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllClients(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            // Authorization check for matchmakers and admins
            if (!in_array($user->role, ['admin', 'matchmaker'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Check if requesting own clients or all clients
            $fetchAll = $request->query('fetch_all', false);

            if ($fetchAll) {
                // First, get all users with role 'client'
                $allClients = User::where('role', 'client')
                    ->with('profile')
                    ->get();

                // Then, get the matchmaker details for those who have them
                $matchmakerDetails = MatchmakerClient::with(['matchmaker:id,name,email'])
                    ->get()
                    ->keyBy('user_id');

                // Combine the data
                $clients = $allClients->map(function($client) use ($matchmakerDetails) {
                    $matchmakerClient = $matchmakerDetails->get($client->id);
                    
                    return [
                        'id' => $client->id,
                        'name' => $client->name,
                        'email' => $client->email,
                        'profile' => $client->profile,
                        'matchmaker' => $matchmakerClient ? [
                            'id' => $matchmakerClient->matchmaker->id ?? null,
                            'name' => $matchmakerClient->matchmaker->name ?? 'Unknown',
                            'email' => $matchmakerClient->matchmaker->email ?? null
                        ] : null,
                        'thumbnail_image' => $matchmakerClient && $matchmakerClient->thumbnail_image ? 
                            Storage::url($matchmakerClient->thumbnail_image) : null,
                        'city' => $matchmakerClient->city ?? ($client->profile->city ?? null),
                        'state' => $matchmakerClient->state ?? ($client->profile->state ?? null),
                        'country' => $matchmakerClient->country ?? ($client->profile->country ?? null),
                        'age' => $matchmakerClient->age ?? ($client->profile->age ?? null),
                        'marital_status' => $matchmakerClient->marital_status ?? ($client->profile->maritalstatus ?? null),
                        'religion' => $matchmakerClient->religion ?? ($client->profile->religion ?? null),
                        'body_type' => $matchmakerClient->body_type ?? ($client->profile->bodytype ?? null),
                        'interests' => $matchmakerClient->interests ?? null,
                        'height_feet' => $matchmakerClient->height_feet ?? ($client->profile->height ?? null),
                        'height_inches' => $matchmakerClient->height_inches ?? ($client->profile->inches ?? null),
                    ];
                })
                ->filter()
                ->values();
            } else {
                // Fetch only matchmaker's own clients
                $clients = MatchmakerClient::where('matchmaker_id', $user->id)
                    ->with(['user' => function($query) {
                        $query->select('id', 'name', 'email');
                    }, 'user.profile'])
                    ->get()
                    ->map(function($client) {
                        return [
                            'id' => $client->user->id,
                            'name' => $client->user->name,
                            'email' => $client->user->email,
                            'profile' => $client->user->profile,
                            'thumbnail_image' => $client->thumbnail_image ? 
                                Storage::url($client->thumbnail_image) : null,
                            'city' => $client->city,
                            'state' => $client->state,
                            'country' => $client->country,
                            'age' => $client->age,
                            'marital_status' => $client->marital_status,
                            'religion' => $client->religion,
                            'body_type' => $client->body_type,
                            'interests' => $client->interests,
                            'height_feet' => $client->height_feet,
                            'height_inches' => $client->height_inches,
                        ];
                    });
            }

            Log::info('Clients fetched successfully', [
                'count' => $clients->count(),
                'fetch_all' => $fetchAll,
                'user_role' => $user->role
            ]);

            return response()->json([
                'success' => true,
                'data' => $clients,
                'message' => 'Clients fetched successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching all clients: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch clients',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
public function getPublicClientsByMatchmakerId($matchmakerId)
{
    try {
        $clients = MatchmakerClient::where('matchmaker_id', $matchmakerId)
            ->with([
                'user:id,name'
            ])
            ->get(['id', 'user_id', 'thumbnail_image', 'age']);

        // Transform the image paths
        $clients->transform(function ($client) {
            if ($client->thumbnail_image) {
                $client->thumbnail_image = Storage::url($client->thumbnail_image);
            } else {
                $client->thumbnail_image = '/default-client-image.jpg'; // Set a default image if none exists
            }
            return [
                'id' => $client->id,
                'name' => $client->user->name,
                'age' => $client->age,
                'thumbnail_image' => $client->thumbnail_image,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $clients,
            'message' => 'Clients fetched successfully'
        ]);

    } catch (\Exception $e) {
        Log::error('Error fetching clients: ' . $e->getMessage(), ['exception' => $e]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch clients',
        ], 500);
    }
}

}
