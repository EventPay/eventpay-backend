<?php

namespace App\Http\Controllers;

use App\Mail\CrewInviteMail;
use App\Models\Crew;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CrewController extends Controller
{
    public function add(Request $request)
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
        };

        $crew = new Crew();
        $crew->event_id = $event->id;
        $crew->user_id = $user->id;
        $crew->save();

        //send email to crew member
        Mail::to($user)->send(new CrewInviteMail($crew));

        return response()->json([
            "success" => "Crew invite created",
        ], 200);

    }

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
        ], 200);

    }
}
