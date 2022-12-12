<?php
use Illuminate\Support\Facades\Auth;
$user = Auth::user();
?>
<p>Welcome
@if(isset($user)){{ $user['firstName'] }}@endif
</p>
@if(isset($user))
<a href="/logout">Logout</a>
@else
<a href="/login">Login</a>
@endif
<br>
@php
print_r($user);
@endphp