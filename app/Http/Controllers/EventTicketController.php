<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventTicketController extends Controller
{

    public function create(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'event_id' => "required",
            "name" => "required",
            "price" => "required | numeric",
            "capacity" => "required | Integer",
            "cover_image" => "required|image",
            "description" => "required |string",
        ]);

        //validation failure check
        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors(),
            ]);
        }

        $validated = $validation->validated();

        //checks if event exists
        if (!Event::find($validated['event_id'])) {
            return response()->json([
                'error' => 'Event does not exist!',
            ]);
        }

        $eventTicket = new EventTicket();
        $eventTicket->name = $validated['name'];
        $eventTicket->price = $validated['price'];
        $eventTicket->capacity = $validated['capacity'];
        $eventTicket->event = $validated['event_id'];
        $eventTicket->cover_image = uploadFileRequest($validated['cover_image'], "ticket", "media");
        $eventTicket->description = $validated['description'];
        $eventTicket->save();

        return response()->json([
            'success' => "Ticket Added Successfully",
        ]);

    }

    public function edit(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            "name" => "required",
            "price" => "required | numeric",
            "capacity" => "required | Integer",
            "cover_image" => "required|image",
            "description" => "required |string",
        ]);

        //validation failure check
        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors(),
            ]);
        }

        $validated = $validation->validated();

        $eventTicket = EventTicket::find($id);
        $eventTicket->name = $validated['name'];
        $eventTicket->price = $validated['price'];
        $eventTicket->capacity = $validated['capacity'];
        $eventTicket->event = $validated['event_id'];
        $eventTicket->cover_image = uploadFileRequest($validated['cover_image'], "ticket", "media");
        $eventTicket->description = $validated['description'];
        $eventTicket->save();

        return response()->json([
            'success' => "Ticket Edited Successfully",
        ]);

    }

    public function destroy(Request $request, $id)
    {
        $eventTicket = EventTicket::find($id);

        if ($eventTicket->delete()) {
            return response()->json([
                "success" => "Ticket Deleted",

            ]);
        } else {
            return response()->json([
                "error" => "Server Error",

            ]);
        }

    }

}
