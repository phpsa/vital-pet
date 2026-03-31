<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invitation $invitation)
    {
    }

    public function build(): self
    {
        $inviterName = $this->invitation->is_staff_invite
            ? config('app.name')
            : ($this->invitation->invitedByUser?->name ?? config('app.name'));

        return $this
            ->subject("You've been invited to join {$inviterName}")
            ->view('emails.invitation', [
                'invitation'  => $this->invitation,
                'inviterName' => $inviterName,
                'registerUrl' => route('register.invited', ['token' => $this->invitation->token]),
            ]);
    }
}
