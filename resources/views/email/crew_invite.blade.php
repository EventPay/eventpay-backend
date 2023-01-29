@component('mail::message')
    # Dear {{ $crew->member->firstname }},


    We are pleased to inform you that {{ $crew->event->organizer->firstname }}has invited you to join the crew for the upcoming
    event, {{ $crew->event->title }}.

    Please let {{ $crew->event->organizer->firstname }} know if you are available and interested in participating.
    
    Best regards,<br>
    {{ config('app.name') }}
@endcomponent
