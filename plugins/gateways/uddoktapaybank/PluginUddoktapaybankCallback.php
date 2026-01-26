<?php

require_once dirname(__FILE__) . '/../uddoktapay/vendor/autoload.php';

use UddoktaPay\Enums\GatewayType;
use UddoktaPay\Handler\BaseCallback;

/**
 * UddoktaPay Bank Gateway Callback Handler
 */
class PluginUddoktapaybankCallback extends BaseCallback
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
