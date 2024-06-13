@component('mail::message')
# New Contact Message

You have received a new contact message from {{ $data['name'] }}.

## Details

- Name: {{ $data['name'] }}
- Email: {{ $data['email'] }}
- Subject: {{ $data['subject'] }}

## Message

{{ $data['message'] }}

@endcomponent
