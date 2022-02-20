@component('mail::message')
# {{ $details['title'] }}

<h3>Anda telah bergabung di akun kubota silahkan login dan reset password anda</h3>
Informasi login :<br/>
Username : {{ $details['username'] }} <br/>
Password : {{ $details['send_email_by_password'] }}
<br/>
<br/>
Thanks,<br>
{{ config('app.name') }}
@endcomponent