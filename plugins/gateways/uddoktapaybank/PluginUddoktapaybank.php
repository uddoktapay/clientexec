<?php

require_once dirname(__FILE__) . '/../uddoktapay/vendor/autoload.php';

use UddoktaPay\Enums\GatewayType;
use UddoktaPay\Handler\BasePlugin;

/**
 * UddoktaPay Bank Gateway Plugin
 */
class PluginUddoktapaybank extends BasePlugin
{
    protected function getGatewayType(): string
    {
        return GatewayType::BANK;
    }

    protected function getPluginName(): string
    {
        return 'uddoktapaybank';
    }
}
