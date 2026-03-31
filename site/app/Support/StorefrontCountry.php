<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Lunar\Models\Country;

class StorefrontCountry
{
    const SESSION_KEY = 'storefront_country_id';
    const SESSION_MANUAL_KEY = 'storefront_country_manually_selected';

    /**
     * Get the current storefront country ID from session.
     */
    public static function id(): ?int
    {
        return session(self::SESSION_KEY) ?? null;
    }

    /**
     * Initialise the session country if not already set.
     * Called once per request from middleware.
     */
    public static function initialise(): void
    {
        if (! static::isEnabled()) {
            return;
        }

        if (session()->has(self::SESSION_KEY)) {
            return;
        }

        // Prefer the authenticated user's stored country
        $user = Auth::user();

        if ($user && $user->country_id) {
            session([
                self::SESSION_KEY => (int) $user->country_id,
                self::SESSION_MANUAL_KEY => false,
            ]);
            return;
        }

        // Fall back to the configured default country (first enabled ISO2)
        $defaultIso2 = (string) (static::configuredIso2s()->first() ?? 'AU');

        $country = Country::where('iso2', $defaultIso2)->first();

        if ($country) {
            session([
                self::SESSION_KEY => (int) $country->id,
                self::SESSION_MANUAL_KEY => false,
            ]);
        }
    }

    /**
     * Sync the session country from the given user after login.
     */
    public static function syncFromUser($user): void
    {
        if (! static::isEnabled()) {
            return;
        }

        if (static::isManuallySelected()) {
            return;
        }

        if ($user && $user->country_id) {
            session([
                self::SESSION_KEY => (int) $user->country_id,
                self::SESSION_MANUAL_KEY => false,
            ]);
        } else {
            // Keep existing session value — user may have already chosen
        }
    }

    /**
     * Set the session country to a specific country ID.
     * Validates the ID is among the configured allowed countries.
     */
    public static function set(int $countryId): bool
    {
        if (! static::isEnabled()) {
            return false;
        }

        $allowed = static::allowedCountryIds();

        if (! in_array($countryId, $allowed, true)) {
            return false;
        }

        session([
            self::SESSION_KEY => $countryId,
            self::SESSION_MANUAL_KEY => true,
        ]);

        return true;
    }

    public static function isManuallySelected(): bool
    {
        return (bool) session(self::SESSION_MANUAL_KEY, false);
    }

    public static function isEnabled(): bool
    {
        if (! config()->has('template.storefront_enabled_country_iso2')) {
            return collect(config('template.storefront_countries', []))->isNotEmpty();
        }

        $raw = config('template.storefront_enabled_country_iso2');

        if ($raw === null || $raw === false) {
            return false;
        }

        return static::configuredIso2s()->isNotEmpty();
    }

    /**
     * Return the allowed country IDs from config.
     */
    public static function allowedCountryIds(): array
    {
        $iso2s = static::configuredIso2s()->values()->toArray();

        if (empty($iso2s)) {
            return [];
        }

        return Country::whereIn('iso2', $iso2s)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }

    /**
     * Return all allowed countries as [id, iso2, name] for display.
     */
    public static function allowedCountries(): \Illuminate\Support\Collection
    {
        $iso2s = static::configuredIso2s();
        $labels = collect(config('template.storefront_country_labels', []));

        if ($iso2s->isEmpty()) {
            return collect();
        }

        return Country::whereIn('iso2', $iso2s->toArray())
            ->get(['id', 'iso2', 'name'])
            ->map(fn ($c) => [
                'id'   => (int) $c->id,
                'iso2' => $c->iso2,
                'name' => (string) ($labels->get($c->iso2) ?: $c->name),
            ])
            ->sortBy(fn ($c) => $iso2s->search($c['iso2']))
            ->values();
    }

    protected static function configuredIso2s(): \Illuminate\Support\Collection
    {
        if (config()->has('template.storefront_enabled_country_iso2')) {
            $raw = config('template.storefront_enabled_country_iso2');

            if ($raw === null || $raw === false) {
                return collect();
            }

            return collect((array) $raw)
                ->map(fn ($iso2) => strtoupper((string) $iso2))
                ->filter()
                ->unique()
                ->values();
        }

        $enabled = collect(config('template.storefront_enabled_country_iso2', []))
            ->map(fn ($iso2) => strtoupper((string) $iso2))
            ->filter()
            ->unique()
            ->values();

        if ($enabled->isNotEmpty()) {
            return $enabled;
        }

        // Backward compatibility with the old structure when the new key is missing.
        return collect(config('template.storefront_countries', []))
            ->pluck('iso2')
            ->map(fn ($iso2) => strtoupper((string) $iso2))
            ->filter()
            ->unique()
            ->values();
    }
}
