<x-mail::message>

 <h3>welcome {{$user_name}}</h3>
 Please enter this verification code to be able to change your password :
 <span style="color: #10b981;"> {{$verification_key}}</span>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
