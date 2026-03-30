<?php

declare(strict_types=1);

namespace App\Enums;

enum ShippingCarrier: string
{
    case NZ_POST = 'nz_post';
    case AUSTRALIA_POST = 'australia_post';

    public function label(): string
    {
        return match ($this) {
            self::NZ_POST => 'NZ Post',
            self::AUSTRALIA_POST => 'Australia Post',
        };
    }

    public function trackingUrl(): ?string
    {
        return match ($this) {
            self::NZ_POST => 'https://www.nzpost.co.nz/tools/tracking/item/',
            self::AUSTRALIA_POST => 'https://auspost.com.au/mypost/track/#/details/',
        };
    }

    public function getTrackingLink(string $trackingNumber): ?string
    {
        $url = $this->trackingUrl();

        if ($url === null) {
            return null;
        }

        return $url.urlencode($trackingNumber);
    }

    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $case) => $case->label(), self::cases())
        );
    }

    public static function tryFromLoose(?string $value): ?self
    {
        $normalized = strtolower(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        $normalized = str_replace(['-', ' '], '_', $normalized);

        $normalized = match ($normalized) {
            'nzpost', 'new_zealand_post' => 'nz_post',
            'australia_post', 'auspost', 'au_post' => 'australia_post',
            default => $normalized,
        };

        return self::tryFrom($normalized);
    }
}