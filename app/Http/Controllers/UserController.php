<?php

namespace App\Http\Controllers;

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
        // Validation successful

        $user = User::find(auth()->user()->id);

        $user->profile_image = uploadFileRequest($validated['profile_image'], 'profile', 'profile');
        $user->save();

        return response()->json([
            'success' => 'Profile image uploaded successfully',
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
            'phone' => 'required|numeric',
            'gender' => 'required',
        ]);

        $user = User::find(auth()->user()->id);
        $user->firstname = $request->input('firstname');
        $user->lastname = $request->input('lastname');
        $user->phone = $request->input('phone');
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
}
