<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Get user information.
     *
     * Retrieves the information of the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @response {
     *     "success": "Retrieval success",
     *     "user": {
     *         "id": 1,
     *         "name": "John Doe",
     *         "email": "john@example.com",
     *         "created_at": "2023-07-06T00:00:00.000000Z",
     *         "updated_at": "2023-07-06T00:00:00.000000Z"
     *     }
     * }
     * @response 401 {
     *     "error": "User not found"
     * }
     */
    public function get(Request $request)
    {
        // Check user existence
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                "error" => "User not found",
            ], 401);
        }

        // User found
        return response()->json([
            "success" => "Retrieval success",
            "user" => $user->getData(),
        ]);
    }

    /**
     * Get user notifications.
     *
     * Retrieves the notifications of the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response {
     *     "success": "Retrieval success",
     *     "notifications": [
     *         {
     *             "id": 1,
     *             "message": "Notification 1",
     *             "created_at": "2023-07-06T00:00:00.000000Z",
     *             "updated_at": "2023-07-06T00:00:00.000000Z"
     *         },
     *         {
     *             "id": 2,
     *             "message": "Notification 2",
     *             "created_at": "2023-07-06T00:00:00.000000Z",
     *             "updated_at": "2023-07-06T00:00:00.000000Z"
     *         },
     *         ...
     *     ]
     * }
     * @response 401 {
     *     "error": "User not found"
     * }
     */
    public function notifications()
    {
        // Check user existence
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                "error" => "User not found",
            ], 401);
        }

        // Get notifications in paginated order
        $notifications = $user->notifications()->orderBy("id", "desc")->paginate(15);

        return response()->json([
            "success" => "Retrieval success",
            "notifications" => $notifications->getCollection(),
        ]);
    }
    /**
     * Upload user profile image.
     *
     * Uploads and updates the profile image of the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam file profile_image The user's profile image (required|image)
     *
     * @response {
     *     "success": "Profile image uploaded successfully"
     * }
     * @response 422 {
     *     "error": {
     *         "profile_image": [
     *             "The profile image field is required."
     *         ]
     *     }
     * }
     */
    public function uploadProfile(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'profile_image' => 'required|image',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors(),
            ], 422);
        }

        $validated = $validation->validated();

        $user = User::find(auth()->user()->id);
        $user->profile_image = uploadFileRequest($validated['profile_image'], 'profile', 'media');
        $user->save();

        return response()->json([
            'success' => 'Profile image uploaded successfully',
        ]);
    }

    /**
     * Upload oranizer cover image.
     *
     * Uploads and updates the cover image of the authenticated organizer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam file cover_image The user's cover_image image (required|image)
     *
     * @response {
     *     "success": "Cover image uploaded successfully"
     * }
     * @response 422 {
     *     "error": {
     *         "cover_image": [
     *             "The cover image field is required."
     *         ]
     *     }
     * }
     */
    public function uploadCoverImage(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'cover_image' => 'required|image',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors(),
            ], 422);
        }

        $validated = $validation->validated();

        $user = User::find(auth()->user()->id);
        $user->cover_image = uploadFileRequest($validated['cover_image'], 'profile', 'media');
        $user->save();

        return response()->json([
            'success' => 'Cover image uploaded successfully',
        ]);
    }

    /**
     * Edit user information.
     *
     * Edits the information of the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam string firstname The user's first name (required)
     * @bodyParam string lastname The user's last name (required)
     * @bodyParam string phone The user's phone number (required|numeric)
     * @bodyParam string gender The user's gender (required)
     *
     * @response {
     *     "success": true
     * }
     * @response 422 {
     *     "error": {
     *         "firstname": [
     *             "The firstname field is required."
     *         ],
     *         "lastname": [
     *             "The lastname field is required."
     *         ],
     *         "phone": [
     *             "The phone field is required."
     *         ],
     *         "gender": [
     *             "The gender field is required."
     *         ]
     *     }
     * }
     */
    public function edit(Request $request)
    {
        $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'bio' => 'nullable',
            'phone' => 'required|numeric',
            'gender' => 'required',
        ]);

        $user = User::find(auth()->user()->id);
        $user->firstname = $request->input('firstname');
        $user->lastname = $request->input('lastname');
        $user->phone = $request->input('phone');
        if ($request->has("bio")) {
            $user->bio = $request->input("bio");
        }

        $user->gender = $request->input('gender');
        $user->save();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Change user password.
     *
     * Changes the password of the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam string previous_password The user's previous password (required)
     * @bodyParam string password The new password (required|confirmed)
     * @bodyParam string password_confirmation The confirmation of the new password (required)
     *
     * @response {
     *     "success": true
     * }
     * @response 422 {
     *     "error": "Incorrect previous password"
     * }
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'previous_password' => 'required',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ]);

        $user = User::find(auth()->user()->id);
        if (!Hash::check($request->input('previous_password'), $user->password)) {
            return response()->json([
                'error' => 'Incorrect previous password',
            ], 422);
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Get user events.
     *
     * Retrieves the events associated with the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response {
     *     "success": "Request Successful",
     *     "events": [
     *         {
     *             "id": 1,
     *             "title": "Event 1",
     *             "startDate": "2023-07-06",
     *             "endDate": "2023-07-07",
     *             "description": "Event 1 description",
     *             "created_at": "2023-07-06T00:00:00.000000Z",
     *             "updated_at": "2023-07-06T00:00:00.000000Z"
     *         },
     *         {
     *             "id": 2,
     *             "title": "Event 2",
     *             "startDate": "2023-07-07",
     *             "endDate": "2023-07-08",
     *             "description": "Event 2 description",
     *             "created_at": "2023-07-07T00:00:00.000000Z",
     *             "updated_at": "2023-07-07T00:00:00.000000Z"
     *         },
     *         ...
     *     ]
     * }
     */
    public function events()
    {
        return response()->json([
            "success" => "Request Successful",
            "events" => auth()->user()->events,
        ]);
    }

    public function getOrganizer($id)
    {

        // Check user existence
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                "error" => "User not found",
            ], 401);
        }

        // User found
        return response()->json([
            "success" => "Retrieval success",
            "user" => $user->getData(),
            "events" => $user->events,
        ]);
    }


    public function editNumber(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'phone' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors(),
            ], 422);
        }

        $user = User::find(auth()->user()->id);
        $user->phone = $request->phone;
        $user->save();

        return response()->json([
            'success' => 'Phone number updated successfully',
        ]);

    }
    

    public function tickets()
    {
        $user = auth()->user();
        $upcoming_events = array();
        $events_attended = array();

        $tickets = Ticket::where("user_id", $user->id)->with(['eventTicket.event'])->get();

        foreach ($tickets as $ticket) {
            // Check if the event's end date is greater than or equal to the current time
            // Also, use !in_array($ticket->eventTicket->event, $upcoming_events) to avoid duplicate events
            if ($ticket->eventTicket->event->endDate >= now() && !in_array($ticket->eventTicket->event, $upcoming_events)) {
                array_push($upcoming_events, $ticket->eventTicket->event);
            }

            if ($ticket->eventTicket->event->endDate <= now() && !in_array($ticket->eventTicket->event, $events_attended)) {
                if ($ticket->status == "USED") {
                    array_push($events_attended, $ticket->eventTicket->event);
                }
            }
        }

        return response()->json([
            "success" => "Request Successful",
            "tickets" => $tickets,
            "upcoming_events" => $upcoming_events,
            "events_attended" => $events_attended,
        ]);
    }

    public function organizer($id)
    {

    }

    public function organizers()
    {
        $user = auth()->user();

        $organizers = User::where("organizer", true)->orderBy("created_at", "asc")->get();

        return response()->json([
            "success" => "Request Successful",
            "organizers" => $organizers,
        ]);

    }

}
