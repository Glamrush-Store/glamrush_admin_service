@component('mail::message')
# Email is configured correctly

Hi {{ $recipientName ?: 'there' }},

This is a test email from the Glamrush admin settings page. If you received this, the current mail configuration can send email successfully.

@component('mail::panel')
Mailer: {{ $mailer ?: 'default' }}  
From: {{ $fromAddress ?: 'not configured' }}  
Sent: {{ $sentAt->toDayDateTimeString() }}
@endcomponent

Thanks,  
{{ config('app.name', 'Glamrush') }}
@endcomponent
