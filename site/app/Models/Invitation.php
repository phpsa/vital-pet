<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    protected $fillable = [
        'email',
        'token',
        'invited_by_user_id',
        'is_staff_invite',
        'used_at',
        'expires_at',
    ];

    protected $casts = [
        'is_staff_invite' => 'boolean',
        'used_at'         => 'datetime',
        'expires_at'      => 'datetime',
    ];

    public static function generate(string $email, ?int $invitedByUserId, bool $isStaffInvite = false): self
    {
        return self::create([
            'email'               => $email,
            'token'               => Str::random(48),
            'invited_by_user_id'  => $invitedByUserId,
            'is_staff_invite'     => $isStaffInvite,
            'expires_at'          => now()->addDays(7),
        ]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function isPending(): bool
    {
        return ! $this->isUsed() && ! $this->isExpired();
    }

    public function markUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    public function invitedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }
}
