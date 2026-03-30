<?php

namespace App\Support;

use App\Enums\ShippingCarrier;

class TrackingLinkHelper
{
    public static function from(?string $company, ?string $trackingNumber): ?string
    {
        $trackingNumber = trim((string) $trackingNumber);

        if ($trackingNumber === '') {
            return null;
        }

        $carrier = ShippingCarrier::tryFromLoose($company);

        if (! $carrier) {
            return null;
        }

        return $carrier->getTrackingLink($trackingNumber);
    }

    public static function labelFrom(?string $company): ?string
    {
        $carrier = ShippingCarrier::tryFromLoose($company);

        if (! $carrier) {
            $fallback = trim((string) $company);

            return $fallback !== '' ? $fallback : null;
        }

        return $carrier->label();
    }
}
