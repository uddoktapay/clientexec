<?php

require_once dirname(__FILE__) . '/../uddoktapay/vendor/autoload.php';

use UddoktaPay\Enums\GatewayType;
use UddoktaPay\Handler\BaseCallback;

/**
 * UddoktaPay Global Gateway Callback Handler
 */
class PluginUddoktapayglobalCallback extends BaseCallback
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
