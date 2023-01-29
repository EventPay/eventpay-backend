@component('mail::message')
    # Hey there {{ $withdrawal->user->firstname }}!

    Good news! Your recent withdrawal request for the amount of N{{ number_format($withdrawal->amount) }} has been given the green light and
    has been paid out successfully.

    We're grateful for your partnership and can't wait to be a part of your future events.

    Best regards,<br>
    {{ config('app.name') }}
@endcomponent
