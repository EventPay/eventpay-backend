<?php

namespace App\Http\Controllers;

use App\Mail\BroadcastMail;
use App\Models\BroadcastEmailQueue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MarketingController extends Controller
{
    public function sendBroadcast(Request $request)
    {
        // Validate the request
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        // Get all users
        $users = User::all();

        // Send email to all users
        foreach ($users as $user) {
 //           Mail::to($user->email)->send(new BroadcastMail($request->title, $request->body));

            $queue = new BroadcastEmailQueue();
            $queue->email = $user->email;
            $queue->title =    $request->title;
            $queue->body = $request->body;
            $queue->processed = 0;
            $queue->save();
        }

        // Redirect or return a response
        return redirect()->back()->with('success', 'Broadcast sent successfully!');
    }
}
