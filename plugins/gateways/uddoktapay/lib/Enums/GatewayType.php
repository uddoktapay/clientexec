<?php

declare(strict_types=1);

namespace UddoktaPay\Enums;

/**
 * Gateway Type Constants
 */
class GatewayType
{
    public const DEFAULT = 'checkout-v2';
    public const MFS = 'checkout-v2/mfs';
    public const BANK = 'checkout-v2/bank';
    public const GLOBAL = 'checkout-v2/global';

    /**
     * Get display name for gateway type
     */
    public static function displayName(string $type): string
    {
        return match ($type) {
            self::DEFAULT => 'UddoktaPay',
            self::MFS => 'UddoktaPay MFS',
            self::BANK => 'UddoktaPay Bank',
            self::GLOBAL => 'UddoktaPay Global',
            default => 'UddoktaPay',
        };
    }

    /**
     * Get plugin name for gateway type
     */
    public static function pluginName(string $type): string
    {
        return match ($type) {
            self::DEFAULT => 'uddoktapay',
            self::MFS => 'uddoktapaymfs',
            self::BANK => 'uddoktapaybank',
            self::GLOBAL => 'uddoktapayglobal',
            default => 'uddoktapay',
        };
    }
}
