<?php

declare(strict_types=1);

namespace UddoktaPay\Handler;

require_once 'modules/admin/models/PluginCallback.php';
require_once 'modules/billing/models/class.gateway.plugin.php';
require_once 'modules/billing/models/Invoice.php';

use PluginCallback;
use Plugin;
use UddoktaPay\Enums\PaymentStatus;
use UddoktaPay\Http\UddoktaPayAPI;
use Exception;

/**
 * Abstract Base Callback for UddoktaPay Gateways
 */
abstract class BaseCallback extends PluginCallback
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
     * Process the payment callback
     */
    public function processCallback()
    {
        $pluginName = $this->getPluginName();

        $cPlugin = new Plugin('', $pluginName, $this->user);
        $apiKey = trim($cPlugin->GetPluginVariable("plugin_{$pluginName}_API KEY"));
        $apiBaseURL = trim($cPlugin->GetPluginVariable("plugin_{$pluginName}_API URL"));

        $uddoktaPay = new UddoktaPayAPI($apiKey, $apiBaseURL);

        try {
            if (isset($_REQUEST['invoice_id']) && !empty($_REQUEST['invoice_id'])) {
                $response = $uddoktaPay->verifyPayment($_REQUEST['invoice_id']);
            } else {
                $response = $uddoktaPay->executePayment();
            }
        } catch (Exception $e) {
            die("Verification Error: " . $e->getMessage());
        }

        $amount = trim($response['amount']);
        $paymentMethod = trim(strtoupper($response['payment_method']));
        $invoiceId = trim($response['metadata']['invoice_id']);
        $currencyCode = $response['metadata']['currency'] ?? 'BDT';

        $price = $amount . " " . $currencyCode;
        $cPlugin = new Plugin($invoiceId, $pluginName, $this->user);
        $cPlugin->setAmount($amount);
        $cPlugin->setAction('charge');

        $status = trim($response['status']);

        if ($status === PaymentStatus::COMPLETED) {
            $transaction = "$paymentMethod payment of $price Successful (Order ID: " . $invoiceId . ")";

            if ($cPlugin->IsUnpaid() == 1) {
                $cPlugin->PaymentAccepted($amount, $transaction);
                $returnURL = \CE_Lib::getSoftwareURL() . "/index.php?fuse=billing&paid=1&controller=invoice&view=invoice&id=" . $invoiceId;
                header("Location: " . $returnURL);
                exit;
            } else {
                return;
            }
        } elseif ($status === PaymentStatus::PENDING) {
            // Payment is pending verification - redirect with pending message
            $returnURL = \CE_Lib::getSoftwareURL() . "/index.php?fuse=billing&pending=1&controller=invoice&view=invoice&id=" . $invoiceId;
            header("Location: " . $returnURL);
            exit;
        } else {
            $transaction = "$paymentMethod payment of $price Failed (Order ID: " . $invoiceId . ")";
            $cPlugin->PaymentRejected($transaction);
            $returnURL = \CE_Lib::getSoftwareURL() . "/index.php?fuse=billing&cancel=1&controller=invoice&view=invoice&id=" . $invoiceId;
            header("Location: " . $returnURL);
            exit;
        }
    }
}
