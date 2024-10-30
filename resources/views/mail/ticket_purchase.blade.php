@component('mail::message')
# Hi {{ $user->firstname }}!

We're excited to have you join the upcoming event on {{ $ticket->eventTicket->event->title }}! 🎉

Here are the details for your ticket:

**Ticket Information:**
- Event: {{ $ticket->eventTicket->event->title }}
- Date: {{ show_date($ticket->eventTicket->event->start_date) }}
- Location: {{ $ticket->eventTicket->event->location }}
- Ticket Code: {{ $ticket->ticket_code }}
- Ticket Name: {{ $ticket->eventTicket->name }}
- Ticket Price: ₦{{ number_format($ticket->eventTicket->price) }}

Please make sure to bring this information with you to the event.

If you have any questions or need assistance, feel free to reply to this email, and we'll be happy to help.

To make your entry to the event even more convenient, we've attached a QR code that you can scan at the entrance:

@component('mail::panel')


<img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{$ticket->ticket_code}}" alt="QR Code" style="display: block; margin: 20px auto;">


@endcomponent

We can't wait to see you at the event. It's going to be an amazing time!

Best regards,

The Attend Team
@endcomponent
