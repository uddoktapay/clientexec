<?php

require_once dirname(__FILE__) . '/../uddoktapay/vendor/autoload.php';

use UddoktaPay\Enums\GatewayType;
use UddoktaPay\Handler\BasePlugin;

/**
 * UddoktaPay Global Gateway Plugin
 */
class PluginUddoktapayglobal extends BasePlugin
{
    protected function getGatewayType(): string
    {
        return GatewayType::GLOBAL;
    }

    protected function getPluginName(): string
    {
        return 'uddoktapayglobal';
    }
}
