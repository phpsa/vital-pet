@props(['title' => config('app.name'), 'preheader' => ''])
<!doctype html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $title }}</title>
    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
    <![endif]-->
    <style>
        body { margin: 0; padding: 0; background-color: #f4f4f5; }
        table { border-spacing: 0; }
        td { padding: 0; }
        img { border: 0; display: block; }
        a { color: #111827; }
        .line { color: #e5e7eb; font-size: 1px; line-height: 1px; max-height: 0; overflow: hidden; mso-hide: all; }
        @media only screen and (max-width: 640px) {
            .wrapper { width: 100% !important; padding: 12px !important; }
            .card { border-radius: 0 !important; }
            .content-cell { padding: 24px 20px !important; }
            .items-table td { display: block; width: 100% !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

    {{-- Preheader text (hidden preview) --}}
    @if($preheader)
        <div style="display:none;overflow:hidden;max-height:0;mso-hide:all;">{{ $preheader }}&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;</div>
    @endif

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" class="wrapper" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;">

                    {{-- Header --}}
                    <tr>
                        <td align="center" style="padding-bottom:20px;">
                            @php
                                $emailLogoPath = app(\App\Settings\ContentSettings::class)->logo_path;
                            @endphp
                            @if ($emailLogoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($emailLogoPath))
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($emailLogoPath) }}"
                                     alt="{{ config('app.name') }}"
                                     width="160"
                                     style="display:block;height:auto;max-height:60px;width:auto;max-width:200px;">
                            @else
                                <span style="font-size:22px;font-weight:700;color:#111827;letter-spacing:-0.5px;text-decoration:none;">
                                    {{ config('app.name') }}
                                </span>
                            @endif
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td class="card" style="background:#ffffff;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;">

                            {{-- Optional coloured top stripe --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="background:#111827;height:4px;font-size:1px;line-height:1px;">&nbsp;</td>
                                </tr>
                            </table>

                            {{-- Body --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td class="content-cell" style="padding:36px 40px;color:#111827;font-size:15px;line-height:1.6;">
                                        {{ $slot }}
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:24px 0 8px;text-align:center;font-size:12px;color:#9ca3af;line-height:1.5;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                            You received this email because you have an account with us.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
