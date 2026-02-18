@extends('emails.layouts.main')

@section('content')
    <div class="greeting">Halo, {{ $user->profile->full_name ?? 'Penghuni' }}! 🔑</div>
    
    <p>Kami menerima permintaan untuk mereset kata sandi akun <strong>Sadewas Hub</strong> Anda.</p>
    
    <div style="text-align: center;">
        <a href="{{ $url }}" class="cta-button">Reset Password</a>
    </div>
    
    <p>Jika Anda tidak meminta reset password, abaikan email ini. Akun Anda tetap aman.</p>
@endsection