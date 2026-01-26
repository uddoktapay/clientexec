<?php

require_once dirname(__FILE__) . '/../uddoktapay/vendor/autoload.php';

use UddoktaPay\Enums\GatewayType;
use UddoktaPay\Handler\BasePlugin;

/**
 * UddoktaPay MFS Gateway Plugin
 */
class PluginUddoktapaymfs extends BasePlugin
{
    protected function getGatewayType(): string
    {
        return GatewayType::MFS;
    }

    protected function getPluginName(): string
    {
        return 'uddoktapaymfs';
    }
}
