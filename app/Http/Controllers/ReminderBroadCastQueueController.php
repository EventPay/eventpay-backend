<?php

namespace App\Http\Controllers;

use App\Mail\TicketReminder;
use App\Models\ReminderBroadCastQueue;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReminderBroadCastQueueController extends Controller
{
    public function sendQueuedEmails()
    {
        // Fetch the next 3 unprocessed emails from the queue
        $emails = ReminderBroadCastQueue::where('processed', false)
            ->take(3)
            ->get();

        foreach ($emails as $emailQueue) {
            try {
                $ticket = Ticket::find($emailQueue->ticket_id);
                $time = $emailQueue->time;
                // Send the email
                Mail::to($ticket->user->email)->send(new TicketReminder($ticket, $time));

                // Mark the email as processed
                $emailQueue->processed = true;
                $emailQueue->save();
            } catch (\Exception $e) {
                // Log the error
                Log::error('Failed to send broadcast email', ['error' => $e->getMessage()]);
                // Mark the email as processed still
                $emailQueue->processed = true;
                $emailQueue->save();
            }
        }

        return response()->json(['status' => 'Emails processed']);
    }
}
