@extends('emails.layouts.main')

@section('content')
    <div class="greeting">Halo, {{ $user->profile->full_name ?? 'Calon Penghuni' }}! 👋</div>
    
    <p>Terima kasih telah mendaftar di <strong>Sadewas Coliving</strong>. Untuk mulai memilih kamar dan menikmati fasilitas kami, mohon verifikasi alamat email Anda.</p>
    
    <div style="text-align: center;">
        <a href="{{ $url }}" class="cta-button">Verifikasi Email Saya</a>
    </div>
    
    <p>Tautan ini hanya berlaku selama 60 menit demi keamanan akun Anda.</p>
@endsection