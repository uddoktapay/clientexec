<?php

declare(strict_types=1);

namespace UddoktaPay\Handler;

require_once 'modules/admin/models/GatewayPlugin.php';

use GatewayPlugin;
use UddoktaPay\Enums\GatewayType;
use UddoktaPay\Http\UddoktaPayAPI;
use Exception;

/**
 * Abstract Base Plugin for UddoktaPay Gateways
 */
abstract class BasePlugin extends GatewayPlugin
{
    /**
     * Get the gateway type for this plugin
     */
    abstract protected function getGatewayType(): string;

    /**
     * Get the plugin name identifier
     */
    abstract protected function getPluginName(): string;

    /**
     * Get the display name for this plugin
     */
    protected function getDisplayName(): string
    {
        return GatewayType::displayName($this->getGatewayType());
    }

    /**
     * Define plugin configuration variables
     */
    public function getVariables()
    {
        return [
            lang("Plugin Name") => [
                "type"        => "hidden",
                "description" => "",
                "value"       => $this->getDisplayName()
            ],
            lang('Signup Name') => [
                'type'        => 'text',
                'description' => lang('Select the name to display in the signup process for this payment type. Example: eCheck or Credit Card.'),
                'value'       => $this->getDisplayName()
            ],
            lang("API KEY") => [
                "type"        => "text",
                "description" => "Enter your API KEY",
                "value"       => ""
            ],
            lang("API URL") => [
                "type"        => "text",
                "description" => "Enter your API URL",
                "value"       => ""
            ]
        ];
    }

    /**
     * Process single payment
     */
    public function singlePayment($params)
    {
        $pluginName = $this->getPluginName();
        $apiKey = $params["plugin_{$pluginName}_API KEY"];
        $apiBaseURL = $params["plugin_{$pluginName}_API URL"];

        $uddoktaPay = new UddoktaPayAPI($apiKey, $apiBaseURL);

        $baseURL = rtrim(\CE_Lib::getSoftwareURL(), '/') . '/';
        $callbackURL = $baseURL . "plugins/gateways/{$pluginName}/callback.php";
        $cancelURL = $params['invoiceviewURLCancel'];

        $invoiceId = $params['invoiceNumber'];
        $amount = $params["invoiceTotal"];
        $firstname = $params['userFirstName'];
        $lastname = $params['userLastName'];
        $email = $params['userEmail'];
        $phone = $params['userPhone'] ?? '';
        $currencyCode = $params['userCurrency'];

        $requestData = [
            'full_name'    => "$firstname $lastname",
            'email'        => $email,
            'phone'        => $phone,
            'amount'       => $amount,
            'currency'     => $currencyCode,
            'metadata'     => [
                'invoice_id' => $invoiceId,
                'currency'   => $currencyCode,
            ],
            'redirect_url' => $callbackURL,
            'return_type'  => 'GET',
            'cancel_url'   => $cancelURL,
            'webhook_url'  => $callbackURL
        ];

        try {
            $paymentUrl = $uddoktaPay->initPayment($requestData, $this->getGatewayType());
            header('Location:' . $paymentUrl);
            exit();
        } catch (Exception $e) {
            die("Initialization Error: " . $e->getMessage());
        }
    }

    /**
     * Process credit (not used)
     */
    public function credit($params) {}
}
