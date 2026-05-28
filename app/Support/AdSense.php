<?php

namespace App\Support;

class AdSense
{
    public static function enabled(): bool
    {
        return (bool) config('adsense.enabled', false);
    }

    public static function clientId(): string
    {
        $id = trim((string) config('adsense.client', ''));
        if ($id === '') {
            return '';
        }
        if (str_starts_with($id, 'ca-pub-')) {
            return $id;
        }
        if (str_starts_with($id, 'pub-')) {
            return 'ca-'.$id;
        }

        return $id;
    }

    public static function slotId(string $placement): string
    {
        return trim((string) (config('adsense.slots.'.$placement) ?? ''));
    }

    public static function placementLabel(string $placement): string
    {
        return (string) (config('adsense.placements.'.$placement.'.label') ?? $placement);
    }

    public static function shouldShowAds(): bool
    {
        if (! self::enabled() || self::clientId() === '') {
            return false;
        }

        if (request()->is('admin', 'admin/*')) {
            return false;
        }

        if (app()->environment('local') && ! config('adsense.show_on_local', false)) {
            return false;
        }

        return true;
    }

    public static function shouldShowPlaceholders(): bool
    {
        if (request()->is('admin', 'admin/*')) {
            return false;
        }

        if (! config('adsense.show_placeholders', true)) {
            return false;
        }

        if (self::shouldShowAds()) {
            return false;
        }

        return self::enabled() || app()->environment('local');
    }

    public static function shouldRenderSlot(string $placement): bool
    {
        if (request()->is('admin', 'admin/*')) {
            return false;
        }

        if (self::shouldShowAds()) {
            return true;
        }

        return self::shouldShowPlaceholders();
    }

    /** Publisher ID without ca-pub- prefix (for ads.txt). */
    public static function publisherIdForAdsTxt(): ?string
    {
        $client = self::clientId();
        if ($client === '') {
            return null;
        }

        return str_starts_with($client, 'ca-pub-')
            ? substr($client, 7)
            : $client;
    }
}
