<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <style>
        /* Reset & Base */
        body { margin: 0; padding: 0; background-color: #f4f4f4; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        table { border-spacing: 0; width: 100%; }
        td { padding: 0; }
        img { border: 0; }
        
        /* Container */
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f4f4; padding-bottom: 40px; }
        .main-content { background-color: #ffffff; margin: 0 auto; width: 100%; max-width: 600px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        
        /* Header - Black & Gold Theme */
        .header { background-color: #1a1a1a; padding: 30px 0; text-align: center; border-bottom: 4px solid #c5a059; }
        .logo { width: 120px; height: auto; }
        
        /* Body */
        .body-section { padding: 40px 30px; color: #333333; line-height: 1.6; font-size: 16px; }
        .greeting { font-size: 20px; font-weight: bold; margin-bottom: 20px; color: #1a1a1a; }
        
        /* Button (CTA) - Gold Color */
        .cta-button { display: inline-block; padding: 14px 30px; background-color: #c5a059; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 25px 0; font-size: 16px; transition: background 0.3s; }
        .cta-button:hover { background-color: #b08d4b; }
        
        /* Footer */
        .footer { background-color: #eeeeee; padding: 20px; text-align: center; font-size: 12px; color: #888888; }
        .footer a { color: #c5a059; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center">
                    <div class="main-content">
                        <div class="header">
                            <img src="{{ asset('images/sc.png') }}" alt="Sadewas Coliving" class="logo">
                        </div>

                        <div class="body-section">
                            @yield('content')
                            
                            <p style="margin-top: 30px; font-size: 14px; color: #666;">
                                Salam hangat,<br>
                                <strong>Tim Sadewas Hub</strong>
                            </p>
                            
                            {{-- Slot untuk URL copy-paste jika tombol tidak bisa diklik --}}
                            @if(isset($actionUrl))
                            <p style="margin-top: 30px; font-size: 12px; color: #999; border-top: 1px solid #eee; padding-top: 20px;">
                                Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut ke browser Anda:<br>
                                <a href="{{ $actionUrl }}" style="color: #c5a059; word-break: break-all;">{{ $actionUrl }}</a>
                            </p>
                            @endif
                        </div>

                        <div class="footer">
                            &copy; {{ date('Y') }} Sadewas Coliving.<br>
                            Jimbaran, Bali, Indonesia.<br>
                            <a href="#">Privasi</a> | <a href="#">Syarat & Ketentuan</a>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>