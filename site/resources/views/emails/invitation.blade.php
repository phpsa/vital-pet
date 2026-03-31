<h2>You've been invited</h2>

<p>Hi there,</p>

<p>
    {{ $inviterName }} has invited you to create an account.
    Click the link below to register — your invitation expires in 7 days.
</p>

<p>
    <a href="{{ $registerUrl }}" style="display:inline-block;padding:12px 24px;background:#000;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;">
        Accept Invitation &amp; Register
    </a>
</p>

<p style="font-size:0.85em;color:#666;">
    If you cannot click the button, copy and paste this link into your browser:<br>
    {{ $registerUrl }}
</p>

<p style="font-size:0.85em;color:#666;">
    This invitation will expire on {{ $invitation->expires_at->format('d M Y') }}.
    If you did not expect this invitation, you can safely ignore this email.
</p>
