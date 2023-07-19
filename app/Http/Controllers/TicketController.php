<?php

namespace App\Http\Controllers;

use App\Mail\TicketPurchaseMail;
use App\Models\Event;
use App\Models\EventTicket;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    /**
     * Purchase event ticket.
     *
     * Generates a payment link for purchasing an event ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @bodyParam  event_ticket_id  int  required  The ID of the event ticket. Example: 1
     *
     * @response {
     *     "success": "Payment Link generated",
     *     "payment_url": "https://example.com/paystack-url"
     * }
     * @response 404 {
     *     "error": "Event Ticket Not Found"
     * }
     * @response 204 {
     *     "error": "Tickets not available"
     * }
     * @response {
     *     "error": "Payment Error"
     * }
     */
    public function purchase(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'event_ticket_id' => "required",
        ]);

        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors(),
            ]);
        }

        $validated = $validation->validated();

        if (!EventTicket::find($validated['event_ticket_id'])) {
            return response()->json([
                'error' => "Event Ticket Not Found",
            ], 404);
        }

        // If event ticket exists

        $eventTicket = EventTicket::find($validated['event_ticket_id']);

        // Check if tickets are still available
        if ($eventTicket->capacity <= 0) {
            // Tickets not available
            return response()->json([
                'error' => "Tickets not available",
            ], 204);
        }

        // Generate paystack URL
        $url = getPaystackUrl($eventTicket, auth()->user());

        if ($url == null) {
            return response()->json([
                'error' => "Payment Error",
            ]);
        }

        return response()->json([
            'success' => "Payment Link generated",
            'payment_url' => "$url",
        ]);
    }

    /**
     * Validate payment for event ticket.
     *
     * Validates the payment for a purchased event ticket.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $event_ticket_id  The ID of the event ticket.
     * @param  int  $user_id  The ID of the user.
     * @return \Illuminate\Http\JsonResponse
     *
     * @response {
     *     "success": "Ticket created successfully"
     * }
     * @response {
     *     "error": "Transaction could not be verified. Kindly contact admin for resolution"
     * }
     * @response {
     *     "error": "Invalid Event Ticket ID"
     * }
     */
    public function validatePayment(Request $request, $event_ticket_id, $user_id)
    {
        $validation = Validator::make($request->all(), [
            'trxref' => "required",
        ]);

        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors(),
            ]);
        }

        $validated = $validation->validated();

        $success = verifyPaystackPayment($validated['trxref']);
        // Transaction not verified by paystack
        if (!$success) {
            return response()->json([
                'error' => "Transaction could not be verified. Kindly contact admin for resolution",
            ]);
        }

        // Transaction verified

        $eventTicket = EventTicket::find($event_ticket_id);
        $user = User::find($user_id);

        if (!$eventTicket) {
            return response()->json([
                'error' => "Invalid Event Ticket ID",
            ]);
        }

        $ticket = new Ticket();
        $ticket->user_id = $user->id;
        $ticket->parent_ticket = $eventTicket->id;
        $ticket->status = "UNUSED";
        $ticket->ticket_code = str_replace(" ", "", substr(strtolower($eventTicket->event->title), 0, 9)) . uniqid();
        $ticket->save();

        // Add revenue and generate transactions
        $event = Event::find($eventTicket->event_id);
        $event->revenue += $eventTicket->price;

        // Transactions
        $organizer_transaction = new Transaction();
        $organizer_transaction->amount = $eventTicket->price;
        $organizer_transaction->description = "Ticket Purchase: " . substr($event->title, 0, 10) . " (" . $eventTicket->name . ")";
        $organizer_transaction->status = "APPROVED";
        $organizer_transaction->user_id = $event->organizer;
        $organizer_transaction->save();

        $user_transaction = new Transaction();
        $user_transaction->amount = $eventTicket->price;
        $user_transaction->description = substr($event->title, 0, 10) . " Ticket purchased (" . $eventTicket->name . ")";
        $user_transaction->status = "APPROVED";
        $user_transaction->user_id = $user->id;
        $user_transaction->save();

        // Send buy email to user
        //Mail::to($user)->send(new TicketPurchaseMail($user, $eventTicket));
        // Return response

        return response()->json([
            "success" => "Ticket created successfully",
        ]);
    }

    /**
     * Validate event ticket.
     *
     * Validates the provided event ticket code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @response {
     *     "success": "Ticket has been validated successfully"
     * }
     * @response {
     *     "error": "Invalid Ticket"
     * }
     * @response {
     *     "error": "Current user is not an organizer"
     * }
     * @response {
     *     "error": "Ticket has already been used"
     * }
     */
    public function validateTicket(Request $request)
    {
        $validation = Validator::make($request->all(), [
            "ticket_code" => "required",
        ]);

        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors(),
            ]);
        }

        $validated = $validation->validated();

        // Get the particular ticket
        $ticket = Ticket::where("ticket_code", $validated['ticket_code'])->first();

        if (!$ticket) {
            return response()->json([
                'error' => "Invalid Ticket",
            ]);
        }

        // Check if the logged-in user is the owner of the event
        $user = auth()->user();
        if (!$user || $user->id !== $ticket->eventTicket->event->organizer) {
            return response()->json([
                'error' => "Current user is not an organizer",
            ]);
        }

        // Proceed to validate ticket
        if ($ticket->status == "USED") {
            return response()->json([
                'error' => "Ticket has already been used",
            ]);
        } else {
            // Notify user that notification was sent

            // Make ticket useless
            return response()->json([
                "success" => "Ticket has been validated successfully",
            ]);
        }
    }
}
