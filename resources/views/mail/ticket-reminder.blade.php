@component('mail::message')
# Hi {{ $ticket->user->firstname }}!

Just a friendly reminder about the upcoming event, **{{ $ticket->eventTicket->event->title }}**, happening today! 🎉

**Event Details:**
- Event: {{ $ticket->eventTicket->event->title }}
- Date: {{ show_date($ticket->eventTicket->event->start_date) }}
- Location: {{ $ticket->eventTicket->event->location }}
- Ticket Code: {{ $ticket->ticket_code }}
- Ticket Price: ₦{{ number_format($ticket->eventTicket->price) }}

Remember to bring this information with you to ensure a smooth entry.

For your convenience, here's the QR code for your ticket that you can scan at the entrance:

@component('mail::panel')
<img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl={{ $ticket->ticket_code }}" alt="QR Code"
style="display: block; margin: 20px auto;">
@endcomponent

We look forward to seeing you at the event! It's going to be a fantastic time!

Best regards,

The Attend Team
@endcomponent
