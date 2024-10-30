<?php

namespace App\Http\Controllers;

use App\Mail\SaleNotification;
use App\Mail\TicketPurchaseMail;
use App\Models\Event;
use App\Models\EventTicket;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        $quantity = 1;

        if ($request->has("quantity")) {
            $quantity = $request->quantity ?? 1;
        }

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
        // dd("omo");

        if ($eventTicket->capacity <= 0) {
            // Tickets not available
            return response()->json([
                'error' => "Tickets not available",
            ], 204);
        }

        if ($eventTicket->price <= 0) {

            $user = Auth::user();

            $ticket = new Ticket();
            $ticket->user_id = $user->id;
            $ticket->parent_ticket = $eventTicket->id;
            $ticket->status = "UNUSED";
            $ticket->amount_paid = $eventTicket->price;
            $ticket->ticket_code = str_replace(" ", "", substr(strtolower($eventTicket->event->title), 0, 9)) . uniqid();
            $ticket->save();

            // Add revenue and generate transactions
            $event = Event::find($eventTicket->event_id);
            $event->revenue += $eventTicket->price * $quantity;
            $event->save();

            // Send buy email to user
            Mail::to($user)->send(new TicketPurchaseMail($user, $ticket));

            return redirect("https://attend.org.ng/my-profile");
        }

        //   dd("omo");
        //check if event time passed

        if ($eventTicket->event->endDate <= now()) {
            return response()->json([
                'error' => "Event has ended",
            ], 204);
        }

        // Generate paystack URL
        $url = getPaystackUrl($eventTicket, auth()->user(), $quantity);

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
    // public function validatePayment(Request $request, $user_id, $event_ticket_id, $quantity)
    // {
    //     $validation = Validator::make($request->all(), [
    //         'trxref' => "required",
    //     ]);

    //     if ($validation->fails()) {
    //         return response()->json([
    //             'error' => $validation->errors(),
    //         ]);
    //     }

    //     $validated = $validation->validated();

    //     $success = verifyPaystackPayment($validated['trxref'], $event_ticket_id);
    //     // Transaction not verified by paystack
    //     if (!$success) {
    //         return response()->json([
    //             'error' => "Transaction could not be verified. Kindly contact admin for resolution",
    //         ]);
    //     }

    //     // Transaction verified

    //     $eventTicket = EventTicket::find($event_ticket_id);
    //     $user = User::find($user_id);

    //     if (!$eventTicket) {
    //         return response()->json([
    //             'error' => "Invalid Event Ticket ID",
    //         ]);
    //     }

    //     for ($number = 1; $number <= $quantity; $number++) {
    //         $ticket = new Ticket();
    //         $ticket->user_id = $user->id;
    //         $ticket->parent_ticket = $eventTicket->id;
    //         $ticket->status = "UNUSED";
    //         $ticket->amount_paid = $eventTicket->price;
    //         $ticket->ticket_code = str_replace(" ", "", substr(strtolower($eventTicket->event->title), 0, 9)) . uniqid();
    //         $ticket->save();

    //         // Add revenue and generate transactions
    //         $event = Event::find($eventTicket->event_id);
    //         $event->revenue += $eventTicket->price * $quantity;
    //         $event->save();

    //         // Send buy email to user
    //         Mail::to($user)->send(new TicketPurchaseMail($user, $ticket));
    //     }

    //     //Reduce the amount available
    //     $eventTicket->capacity -= $quantity ?? 1;
    //     $eventTicket->save();

    //     // Transactions
    //     $organizer_transaction = new Transaction();
    //     $organizer_transaction->amount = $eventTicket->price * $quantity;
    //     $organizer_transaction->description = "Ticket Purchase: " . substr($event->title, 0, 10) . " (" . $eventTicket->name . ")";
    //     $organizer_transaction->status = "APPROVED";
    //     $organizer_transaction->user_id = $event->organizer;
    //     $organizer_transaction->save();

    //     $user_transaction = new Transaction();
    //     $user_transaction->amount = $eventTicket->price * $quantity;
    //     $user_transaction->description = substr($event->title, 0, 10) . " Ticket purchased (" . $eventTicket->name . ")";
    //     $user_transaction->status = "APPROVED";
    //     $user_transaction->user_id = $user->id;
    //     $user_transaction->save();

    //     // Return response

    //     return redirect("https://attend.org.ng/my-profile");
    // }

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
            "auth_key" => "required",
        ]);

        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors(),
            ]);
        }

        $validated = $validation->validated();

        // Get the particular ticket
        $ticket = Ticket::where("ticket_code", $validated['ticket_code'])->with("eventTicket")->get()->first();

        if (!$ticket) {
            return response()->json([
                'error' => "Invalid Ticket",
            ]);
        }

        //   return $request->input("auth_key")." and the ".$ticket->eventTicket->event->auth_key;

        if ($ticket->eventTicket->event->auth_key != $request->input("auth_key")) {
            return response()->json([
                'error' => "You are not allowed to access this page!",
            ]);
        }

        // Proceed to validate ticket
        if ($ticket->status == "USED") {
            return response()->json([
                'error' => "Ticket has already been used",
                "ticket" => $ticket,
            ]);
        } else {
            // Make ticket useless
            $ticket->status = "USED";
            $ticket->save();
            return response()->json([
                "success" => "Ticket has been validated successfully",
                "ticket" => $ticket,
            ]);
        }
    }

    public function adminPurchase(Request $request)
    {
        $request->validate([
            'event_ticket' => 'required|exists:event_tickets,id',
            'user_email' => 'required|email',
            'quantity' => 'required|integer',
        ]);

        $eventTicket = EventTicket::findOrFail($request->input('event_ticket'));
        $userEmail = $request->input('user_email');
        $quantity = $request->input("quantity") ?? 1;

        //Find User

        $user = User::where("email", $userEmail)->get()->first();
        if (!$user) {
            return back()->with("error", "No user found");
        }
        for ($number = 1; $number <= $quantity; $number++) {
            $ticket = new Ticket();
            $ticket->user_id = $user->id;
            $ticket->parent_ticket = $eventTicket->id;
            $ticket->status = "UNUSED";
            $ticket->amount_paid = $eventTicket->price;
            $ticket->ticket_code = str_replace(" ", "", substr(strtolower($eventTicket->event->title), 0, 9)) . uniqid();
            $ticket->save();

            // Add revenue and generate transactions
            $event = Event::find($eventTicket->event_id);
            $event->revenue += $eventTicket->price * $quantity;
            $event->save();

            Mail::to($user)->send(new TicketPurchaseMail($user, $ticket));
        }

        //Reduce the amount available
        $eventTicket->capacity -= $quantity ?? 1;
        $eventTicket->save();

        // Transactions
        $organizer_transaction = new Transaction();
        $organizer_transaction->amount = $eventTicket->price * $quantity;
        $organizer_transaction->description = "Ticket Purchase: " . substr($event->title, 0, 10) . " (" . $eventTicket->name . ")";
        $organizer_transaction->status = "APPROVED";
        $organizer_transaction->user_id = $event->organizer;
        $organizer_transaction->save();

        $user_transaction = new Transaction();
        $user_transaction->amount = $eventTicket->price * $quantity;
        $user_transaction->description = substr($event->title, 0, 10) . " Ticket purchased (" . $eventTicket->name . ")";
        $user_transaction->status = "APPROVED";
        $user_transaction->user_id = $user->id;
        $user_transaction->save();

        // Send buy email to user

        // Return response

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Ticket purchased successfully!');
    }

    // public function sendReminder()
    // {

    //     $event = Event::find(8);

    //     $tickets = $event->attendees()->get();
    //     $count = 0;

    //     foreach ($tickets as $ticket) {
    //         Mail::to($ticket->user)->send(new TicketReminder($ticket));
    //         $count++;
    //     }

    //     return "Reminder Email sent with count" . $count;

    // }

    public function webhookVerify(Request $request)
    {
        // Retrieve the request's body and signature header
        $input = $request->all();
        $paystackSignature = $request->header('x-paystack-signature');

        // Verify the request is from Paystack
        $secretKey = env('PAYSTACK_KEY');
        $hash = hash_hmac('sha512', $request->getContent(), $secretKey);

        if ($hash !== $paystackSignature) {
            // Log the invalid attempt
            Log::warning('Invalid Paystack Webhook Signature');
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
        }

        // Handle the event
        $event = $input['event'];

        if ($event === 'charge.success') {
            $data = $input['data'];
            $reference = $data['reference'];

            // Verify the payment status with Paystack
            $verificationResponse = $this->verifyTransaction($reference);

            if ($verificationResponse['status'] && $verificationResponse['data']['status'] === 'success') {
                // Payment is successful, perform your logic here
                $user_id = $data['metadata']['user_id'];
                $event_ticket_id = $data['metadata']['event_ticket_id'];
                $quantity = $data['metadata']['quantity'];

                Log::info($data);

                // Purchase the ticket
                $eventTicket = EventTicket::find($event_ticket_id);
                $user = User::find($user_id);

                if (!$eventTicket) {
                    return response()->json([
                        'error' => "Invalid Event Ticket ID",
                    ]);
                }

                //Send notification to admin

                try {
                    Mail::to($eventTicket->event->user)->send(new SaleNotification($eventTicket, $user, $quantity));
                } catch (Exception $e) {

                }

                for ($number = 1; $number <= $quantity; $number++) {
                    $ticket = new Ticket();
                    $ticket->user_id = $user->id;
                    $ticket->parent_ticket = $eventTicket->id;
                    $ticket->status = "UNUSED";
                    $ticket->amount_paid = $eventTicket->price;
                    $ticket->ticket_code = str_replace(" ", "", substr(strtolower($eventTicket->event->title), 0, 9)) . uniqid();
                    $ticket->save();

                    // Add revenue and generate transactions
                    $event = Event::find($eventTicket->event_id);
                    $event->revenue += $eventTicket->price * $quantity;
                    $event->save();

                    //Catching error that doesn't send back the 200 response code for the server.
                    try {
                        // Send buy email to user
                        Mail::to($user)->send(new TicketPurchaseMail($user, $ticket));

                    } catch (Exception $e) {
                        Log::info("Failed to send email to" . $user->email);
                        return response()->json(['status' => 'success', 'message' => 'Ticket mail could not be sent'], 200);
                    }

                }

                // Reduce the amount available
                $eventTicket->capacity -= $quantity ?? 1;
                $eventTicket->save();

                // Transactions
                $organizer_transaction = new Transaction();
                $organizer_transaction->amount = $eventTicket->price * $quantity;
                $organizer_transaction->description = "Ticket Purchase: " . substr($event->title, 0, 10) . " (" . $eventTicket->name . ")";
                $organizer_transaction->status = "APPROVED";
                $organizer_transaction->user_id = $event->organizer;
                $organizer_transaction->save();

                $user_transaction = new Transaction();
                $user_transaction->amount = $eventTicket->price * $quantity;
                $user_transaction->description = substr($event->title, 0, 10) . " Ticket purchased (" . $eventTicket->name . ")";
                $user_transaction->status = "APPROVED";
                $user_transaction->user_id = $user->id;
                $user_transaction->save();

                Log::info("Ticket purchase successful");

                return response()->json(['status' => 'success', 'message' => 'Payment successful'], 200);
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Event received'], 200);
    }

    private function verifyTransaction($reference)
    {
        $url = "https://api.paystack.co/transaction/verify/{$reference}";
        $secretKey = env('PAYSTACK_KEY');

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$secretKey}",
        ])->get($url);

        return $response->json();
    }

}
