<?php

namespace App\Support;

/**
 * Template/Theme Helper
 *
 * Provides convenient access to active template and theme configuration.
 */
class TemplateHelper
{
    /**
     * Get the active template name
     */
    public static function active(): string
    {
        return config('template.active');
    }

    /**
     * Get the full theme configuration for the active template
     */
    public static function theme(): array
    {
        return config('template.themes.' . self::active(), []);
    }

    /**
     * Get a specific theme configuration value
     *
     * @param string $key The configuration key (e.g., 'logo', 'brand_color')
     * @param mixed $default The default value if key doesn't exist
     */
    public static function themeConfig(string $key, $default = null)
    {
        return config('template.themes.' . self::active() . '.' . $key, $default);
    }

    /**
     * Get the image directory path for the active template
     */
    public static function imageDir(): string
    {
        return self::themeConfig('image_directory', 'img/');
    }

    /**
     * Get the theme name
     */
    public static function themeName(): string
    {
        return self::themeConfig('name', 'Store');
    }

    /**
     * Get the brand color for the active template
     */
    public static function brandColor(): string
    {
        return self::themeConfig('brand_color', '#000000');
    }

    /**
     * Get the logo path for the active template
     */
    public static function logo(): string
    {
        return self::themeConfig('logo', 'img/logo.svg');
    }

    /**
     * Get the hero image path for the active template
     */
    public static function heroImage(): string
    {
        return self::themeConfig('hero_image', 'img/hero.jpg');
    }

    /**
     * Check if a template is the active one
     */
    public static function is(string $template): bool
    {
        return self::active() === $template;
    }

    /**
     * Check if petstore is active
     */
    public static function isPetstore(): bool
    {
        return self::is('petstore');
    }

    /**
     * Check if memberstore (peptide) is active
     */
    public static function ismemberstore(): bool
    {
        return self::is('memberstore');
    }

    /**
     * Check if bluestore is active
     */
    public static function isBluestore(): bool
    {
        return self::is('bluestore');
    }
}
