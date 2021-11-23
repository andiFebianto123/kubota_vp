@component('mail::message')
# {{ $details['title'] }}

<h3>{{ $details['message'] }}</h3>

@if($details['type'] == 'reminder_po')
@component('mail::button', ['url' => $details['url_button']])
    Detail PO
@endcomponent
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent