<?php

namespace App\Http\Controllers;

use App\Mail\BroadcastMail;
use App\Models\BroadcastEmailQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BroadcastEmailQueueController extends Controller
{
    public function sendQueuedEmails()
    {
        // Fetch the next 3 unprocessed emails from the queue
        $emails = BroadcastEmailQueue::where('processed', false)
            ->take(3)
            ->get();

        foreach ($emails as $emailQueue) {
            try {
                // Send the email
                Mail::to($emailQueue->email)->send(new BroadcastMail($emailQueue->title, $emailQueue->body));

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
