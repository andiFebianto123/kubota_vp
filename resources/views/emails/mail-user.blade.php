@component('mail::message')
# {{ $details['title'] }}

<h3>Anda telah bergabung di akun kubota silahkan login dan reset password anda</h3>
Informasi login :<br/>
Username : {{ $details['username'] }} <br/>
Password : {{ $details['send_email_by_password'] }}
<p>
<small>
    <i>
    Kata sandi harus berisi minimal 8 karakter, satu karakter huruf besar, satu karakter huruf kecil, satu angka, dan satu karakter khusus
    </i>
</small>
</p>
<br/>

<br/>
Thanks,<br>
{{ config('app.name') }}
@endcomponent