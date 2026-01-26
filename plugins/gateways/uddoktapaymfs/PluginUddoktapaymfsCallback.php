<?php

require_once dirname(__FILE__) . '/../uddoktapay/vendor/autoload.php';

use UddoktaPay\Enums\GatewayType;
use UddoktaPay\Handler\BaseCallback;

/**
 * UddoktaPay MFS Gateway Callback Handler
 */
class PluginUddoktapaymfsCallback extends BaseCallback
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
