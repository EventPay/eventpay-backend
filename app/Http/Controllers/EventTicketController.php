<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventTicketController extends Controller
{

    /**
     * Create a new event ticket.
     *
     * Creates a new event ticket associated with the specified event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @bodyParam event_id int required The ID of the associated event.
     * @bodyParam name string required The name of the ticket.
     * @bodyParam price float required The price of the ticket.
     * @bodyParam capacity int required The capacity of the ticket.
     * @bodyParam cover_image file required The cover image for the ticket.
     * @bodyParam description string required The description of the ticket.
     * @return \Illuminate\Http\JsonResponse
     *
     * @response {
     *     "success": "Ticket Added Successfully"
     * }
     * @response 422 {
     *     "error": {
     *         "name": [
     *             "The name field is required."
     *         ],
     *         "price": [
     *             "The price field is required."
     *         ],
     *         "capacity": [
     *             "The capacity field is required."
     *         ],
     *         "cover_image": [
     *             "The cover image field is required."
     *         ],
     *         "description": [
     *             "The description field is required."
     *         ]
     *     }
     * }
     * @response 404 {
     *     "error": "Event does not exist!"
     * }
     */
    public function create(Request $request)
    {
        // Validation
        $validation = Validator::make($request->all(), [
            'event_id' => 'required',
            'name' => 'required',
            'price' => 'required|numeric',
            'capacity' => 'required|integer',
            'cover_image' => 'required|image',
            'description' => 'required|string',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors(),
            ], 422);
        }

        $validated = $validation->validated();

        // Check if event exists
        if (!Event::find($validated['event_id'])) {
            return response()->json([
                'error' => 'Event does not exist!',
            ], 404);
        }

        $eventTicket = new EventTicket();
        $eventTicket->name = $validated['name'];
        $eventTicket->price = $validated['price'];
        $eventTicket->capacity = $validated['capacity'];
        $eventTicket->event = $validated['event_id'];
        $eventTicket->cover_image = uploadFileRequest($validated['cover_image'], 'ticket', 'media');
        $eventTicket->description = $validated['description'];
        $eventTicket->save();

        return response()->json([
            'success' => 'Ticket Added Successfully',
        ]);
    }


    /**
     * Edit an event ticket.
     *
     * Edits the details of the specified event ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  The ID of the ticket to edit.
     * @bodyParam name string required The name of the ticket.
     * @bodyParam price float required The price of the ticket.
     * @bodyParam capacity int required The capacity of the ticket.
     * @bodyParam cover_image file required The cover image for the ticket.
     * @bodyParam description string required The description of the ticket.
     * @return \Illuminate\Http\JsonResponse
     *
     * @response {
     *     "success": "Ticket Edited Successfully"
     * }
     * @response 422 {
     *     "error": {
     *         "name": [
     *             "The name field is required."
     *         ],
     *         "price": [
     *             "The price field is required."
     *         ],
     *         "capacity": [
     *             "The capacity field is required."
     *         ],
     *         "cover_image": [
     *             "The cover image field is required."
     *         ],
     *         "description": [
     *             "The description field is required."
     *         ]
     *     }
     * }
     */
    public function edit(Request $request, $id)
    {
        // Validation
        $validation = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric',
            'capacity' => 'required|integer',
            'cover_image' => 'required|image',
            'description' => 'required|string',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors(),
            ], 422);
        }

        $validated = $validation->validated();

        $eventTicket = EventTicket::find($id);
        $eventTicket->name = $validated['name'];
        $eventTicket->price = $validated['price'];
        $eventTicket->capacity = $validated['capacity'];
        $eventTicket->cover_image = uploadFileRequest($validated['cover_image'], 'ticket', 'media');
        $eventTicket->description = $validated['description'];
        $eventTicket->save();

        return response()->json([
            'success' => 'Ticket Edited Successfully',
        ]);
    }


    /**
     * Delete an event ticket.
     *
     * Deletes the specified event ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id  The ID of the ticket to delete.
     * @return \Illuminate\Http\JsonResponse
     *
     * @response {
     *     "success": "Ticket Deleted"
     * }
     * @response 404 {
     *     "error": "Server Error"
     * }
     */
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
            ], 404);
        }
    }
}
