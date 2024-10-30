@component('mail::message')
# You've Just Sold a Ticket!

Hey {{ $eventTicket->event->user->firstname }},

Great news! 🎉 You've just sold a ticket for **{{ $eventTicket->event->title }}**.

### Ticket Details:
- **Buyer Name:** {{ $user->firstname }} {{ $user->lastname }}
- **Ticket Type:** {{ $eventTicket->name }}
- **Quantity:** ₦{{ $quantity }}
- **Sale Amount:** ₦{{ $quantity * $eventTicket->price }}

Thanks for choosing us to help make your event a success. We’re excited to see how it all comes together!

Cheers,<br>
The Attend Team
@endcomponent
