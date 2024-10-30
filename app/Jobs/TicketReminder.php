<?php

namespace App\Jobs;

use App\Mail\TicketReminder as MailTicketReminder;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class TicketReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $ticket;
    public $time;

    public function __construct($ticket,$time)
    {
     $this->ticket = $ticket;
     $this->time = $time;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //Mail::to($this->ticket->user->email)->send(new MailTicketReminder($this->ticket,ltrim($this->time, '-')));
        Mail::to("frankostein96@gmail.com")->send(new MailTicketReminder($this->ticket,ltrim($this->time, '-')));
    }
}
