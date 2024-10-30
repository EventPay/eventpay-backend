<?php

namespace App\Console;

use App\Jobs\ChangeEventEndedStatus;
use App\Jobs\TicketReminder;
use App\Models\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->job(new ChangeEventEndedStatus)->everyFiveMinutes();

        $events = Event::where("startDate","<",now())->where("status","PENDING")->get();
        foreach ($events as $event) {

            $eventTime = Carbon::parse($event->startDate)->setTimezone('Africa/Lagos');

            $reminders = [
                '3 days' => $eventTime->copy()->subDays(3),
                '2 days' => $eventTime->copy()->subDays(2),
                '24 hours' => $eventTime->copy()->subDay(),
                '6 hours' => $eventTime->copy()->subHours(6),
                '1 hour' => $eventTime->copy()->subHour(),
            ];

            foreach ($reminders as $key => $time) {
                // Remove the '-' sign from the key to pass it cleanly
                $cleanKey = ltrim($key, '-');

                $schedule->call(function () use ($event, $time, $cleanKey) {
                    $tickets = $event->attendees;
                    foreach ($tickets as $ticket) {
                        TicketReminder::dispatch($ticket, $time);
                    }
                })->when($time);
            }
        }
        }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
