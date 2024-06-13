<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CrewController extends Controller
{
    /**
     * Add crew member to event.
     *
     * Adds a crew member to the specified event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam  event_id  integer required The ID of the event. Example: 1
     * @bodyParam  username  string required The username of the crew member. Example: john_doe
     *
     * @response {
     *     "success": "Crew invite created"
     * }
     * @response 400 {
     *     "error": {
     *         "event_id": [
     *             "The event_id field is required."
     *         ],
     *         "username": [
     *             "The username field is required."
     *         ]
     *     }
     * }
     * @response 400 {
     *     "error": "Event does not exist!"
     * }
     * @response 400 {
     *     "error": "User does not exist!"
     * }
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "event_id" => "required|integer",
            "username" => "required|string",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        $user = User::where("username", $request->username)->first();
        $event = Event::find($request->event_id);

        if (!$event) {
            return response()->json([
                'error' => "Event does not exist!",
            ], 400);
        }

        if (!$user || $user->suspended == true) {
            return response()->json([
                'error' => "User does not exist!",
            ], 400);
        }

        $crew = new Crew();
        $crew->event_id = $event->id;
        $crew->user_id = $user->id;
        $crew->save();

        // Send email to crew member
        //Mail::to($user)->send(new CrewInviteMail($crew));

        return response()->json([
            "success" => "Crew invite created",
        ]);
    }

    /**
     * Remove crew member from event.
     *
     * Removes a crew member from the specified event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam  crew_id  integer required The ID of the crew member. Example: 1
     *
     * @response {
     *     "success": "Crew entry deleted"
     * }
     * @response 400 {
     *     "error": {
     *         "crew_id": [
     *             "The crew_id field is required."
     *         ]
     *     }
     * }
     * @response 400 {
     *     "error": "Crew entry does not exist!"
     * }
     */
    public function remove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "crew_id" => "required|integer",
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        $crew = Crew::find($request->crew_id);

        if (!$crew) {
            return response()->json([
                'error' => "Crew entry does not exist!",
            ], 400);
        }

        $crew->delete();

        return response()->json([
            "success" => "Crew entry deleted",
        ]);
    }
}
