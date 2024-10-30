<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChangeEventEndedStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $now = now();

        // Update pending events to live if the event is still active
        Event::where('status', 'PENDING')
            ->where('startDate', '<=', $now)
            ->where('endDate', '>', $now)
            ->update(['status' => 'LIVE']);

        // Update live and pending events to finished if the event has ended
        Event::whereIn('status', ['LIVE', 'PENDING'])
            ->where('endDate', '<', $now)
            ->update(['status' => 'FINISHED']);
    }
}
