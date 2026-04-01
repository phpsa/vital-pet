<x-email.layout title="You've been invited" preheader="{{ $inviterName }} has invited you to create an account.">

    <p style="margin:0 0 20px;font-size:18px;font-weight:600;color:#111827;">
        You've been invited
    </p>

    <p style="margin:0 0 24px;color:#374151;">
        <strong style="color:#111827;">{{ $inviterName }}</strong> has invited you to create an account.
        Click the button below to accept — this invitation expires in 7 days.
    </p>

    {{-- CTA button --}}
    <table role="presentation" cellspacing="0" cellpadding="0" style="margin-bottom:28px;">
        <tr>
            <td style="border-radius:6px;background:#111827;">
                <a href="{{ $registerUrl }}"
                   style="display:inline-block;padding:13px 28px;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;border-radius:6px;letter-spacing:0.01em;">
                    Accept Invitation &amp; Register
                </a>
            </td>
        </tr>
    </table>

    {{-- Expiry & fallback --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:16px 20px;">
                <p style="margin:0 0 8px;font-size:13px;color:#6b7280;">
                    <strong style="color:#374151;">Expires:</strong> {{ $invitation->expires_at->format('d M Y') }}
                </p>
                <p style="margin:0;font-size:12px;color:#9ca3af;">
                    If the button doesn't work, copy and paste this link into your browser:<br>
                    <span style="color:#374151;word-break:break-all;">{{ $registerUrl }}</span>
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:24px 0 0;font-size:13px;color:#9ca3af;">
        If you weren't expecting this invitation, you can safely ignore this email.
    </p>

</x-email.layout>
