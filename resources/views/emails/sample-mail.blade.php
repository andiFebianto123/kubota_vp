@component('mail::message')
# {{ $details['title'] }}

<h3>{{ $details['message'] }}</h3>

@if($details['type'] == 'reminder_po')
@component('mail::button', ['url' => $details['url_button']])
    Detail PO
@endcomponent
@elseif($details['type'] == 'forgot_password')
@component('mail::button', ['url' => $details['fp_url']])
    Reset Password
@endcomponent
@elseif($details['type'] == 'otp')
@component('mail::button', ['url' => $details['otp_url'], 'type' => 'OTP'])
    {!! $details['otp_code'] !!}
@endcomponent
@endif

Thanks,<br>
{{ config('app.name') }}
@endcomponent