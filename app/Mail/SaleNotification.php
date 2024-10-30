<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SaleNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

     public $eventTicket;
     public $quantity;
     public $user;

    public function __construct($eventTicket,$user,$quantity)
    {
        $this->eventTicket = $eventTicket;
        $this->user = $user;
        $this->quantity = $quantity;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mail.sale-notification');
    }
}
