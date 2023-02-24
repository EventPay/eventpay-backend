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

        //if event ticket exists

        $eventTicket = EventTicket::find($validated['event_ticket_id']);

        //check if tickets still available
        if ($eventTicket->capacity <= 0) {
            //ticket not available
            return response()->json([
                'error' => "Tickets not available",
            ], 204);
        }

        //generate paystack url

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
        //transaction not verified by paystack
        if (!$success) {
            return response()->json([
                'error' => "Transaction could not be verified. Kindly contact admin for resolution",
            ]);
        }

        //transaction verified

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
        //      print_r($eventTicket->event);

        $ticket->ticket_code = str_replace(" ", "", substr(strtolower($eventTicket->event->title), 0, 9)) . uniqid();
        $ticket->save();

        //add revenue and generate transactions
        $event = Event::find($eventTicket->event_id);
        $event->revenue += $eventTicket->price;

        //transactons
        $organizer_transaction = new Transaction();
        $organizer_transaction->amount = $eventTicket->price;
        $organizer_transaction->description = "Ticket Purchase : " . substr($event->title, 0, 10) . " (" . $eventTicket->name . ")";
        $organizer_transaction->status = "APPROVED";
        $organizer_transaction->user_id = $event->organizer;
        $organizer_transaction->save();

        $user_transaction = new Transaction();
        $user_transaction->amount = $eventTicket->price;
        $user_transaction->description = substr($event->title, 0, 10) . " Ticket purchased (" . $eventTicket->name . ")";
        $user_transaction->status = "APPROVED";
        $user_transaction->user_id = $user->id;
        $user_transaction->save();

        //send buy email to user
        Mail::to($user)->send(new TicketPurchaseMail($user, $eventTicket));
        //return redirect to user dashboard

        return response()->json([
            "success" => "Ticket created successfully",
        ]);

    }

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

        //get the particular ticket

        $ticket = Ticket::where("ticket_code", $validated['ticket_code'])->get()->first();

        if (!$ticket) {
            return response()->json([
                'error' => "Invalid Ticket",
            ]);
        }

        //checks if logged in user is the owner of the event
        $user = auth()->user();
        if (!$user->id == $ticket->eventTicket->event->organizer) {
            return response()->json([
                'error' => "Current user is not an organizer",
            ]);
        }

        //proceed to validate ticket

        if ($ticket->status == "USED") {
            return response()->json([
                'error' => "Ticket has already been used",
            ]);
        } else {

            //Notify user that notification was sent
            

            //make ticket useless
            return response()->json([
                'success' => "Ticket has been validated successfully",
            ]);
        }

    }
}
