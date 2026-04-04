<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Lunar\Models\CustomerGroup;

class Invitation extends Model
{
    protected $fillable = [
        'email',
        'token',
        'invited_by_user_id',
        'is_staff_invite',
        'customer_group_id',
        'used_at',
        'expires_at',
    ];

    protected $casts = [
        'is_staff_invite' => 'boolean',
        'used_at'         => 'datetime',
        'expires_at'      => 'datetime',
    ];

    public static function generate(string $email, ?int $invitedByUserId, bool $isStaffInvite = false, ?int $customerGroupId = null): self
    {
        return self::create([
            'email'               => $email,
            'token'               => Str::random(48),
            'invited_by_user_id'  => $invitedByUserId,
            'is_staff_invite'     => $isStaffInvite,
            'customer_group_id'   => $customerGroupId,
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

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }
}
