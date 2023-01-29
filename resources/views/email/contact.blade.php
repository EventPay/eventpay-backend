@component('mail::message')
# New Contact Message

You have received a new contact message from {{ $name }}.

## Details

- Name: {{ $name }}
- Email: {{ $email }}
- Subject: {{ $subject }}

## Message

{{ $message }}

@endcomponent
