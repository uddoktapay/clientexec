<?php

declare(strict_types=1);

namespace UddoktaPay\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Exception;

/**
 * UddoktaPay Payment Gateway API Client
 */
class UddoktaPayAPI
{
    private const API_HEADER_KEY = 'RT-UDDOKTAPAY-API-KEY';
    private const DEFAULT_TIMEOUT = 30;
    private const VERIFY_ENDPOINT = 'verify-payment';

    private string $apiKey;
    private string $apiBaseURL;
    private Client $client;

    public function __construct(string $apiKey, string $apiBaseURL)
    {
        $this->apiKey = trim($apiKey);
        $this->apiBaseURL = $this->normalizeBaseURL($apiBaseURL);

        $this->client = new Client([
            'base_uri' => $this->apiBaseURL . '/',
            'timeout' => self::DEFAULT_TIMEOUT,
            'verify' => true,
            'headers' => [
                self::API_HEADER_KEY => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Initialize a payment session
     */
    public function initPayment(array $requestData, string $apiType): string
    {
        $response = $this->sendRequest('POST', $apiType, $requestData);

        if (!isset($response['payment_url'])) {
            throw new Exception($response['message'] ?? 'Payment initialization failed');
        }

        return $response['payment_url'];
    }

    /**
     * Verify a payment by invoice ID
     */
    public function verifyPayment(string $invoiceId): array
    {
        if (trim($invoiceId) === '') {
            throw new Exception('Invoice ID cannot be empty');
        }

        return $this->sendRequest('POST', self::VERIFY_ENDPOINT, ['invoice_id' => $invoiceId]);
    }

    /**
     * Execute payment from IPN webhook
     */
    public function executePayment(): array
    {
        $headerKey = 'HTTP_' . str_replace('-', '_', self::API_HEADER_KEY);
        $headerApi = $_SERVER[$headerKey] ?? null;

        if ($headerApi === null) {
            throw new Exception('Missing API key in request header');
        }

        if ($headerApi !== $this->apiKey) {
            throw new Exception('Invalid API key - Unauthorized');
        }

        $rawInput = trim(file_get_contents('php://input') ?: '');

        if ($rawInput === '') {
            throw new Exception('Empty IPN response body');
        }

        $data = json_decode($rawInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON in IPN response: ' . json_last_error_msg());
        }

        if (!isset($data['invoice_id'])) {
            throw new Exception('Invoice ID missing in IPN data');
        }

        return $this->verifyPayment($data['invoice_id']);
    }

    /**
     * Normalize the API base URL
     */
    private function normalizeBaseURL(string $apiBaseURL): string
    {
        if ($apiBaseURL === '') {
            throw new Exception('API Base URL cannot be empty');
        }

        $baseURL = rtrim($apiBaseURL, '/');
        $apiSegmentPosition = strpos($baseURL, '/api');

        if ($apiSegmentPosition !== false) {
            $baseURL = substr($baseURL, 0, $apiSegmentPosition + 4);
        }

        return $baseURL;
    }

    /**
     * Send HTTP request to API
     */
    private function sendRequest(string $method, string $endpoint, array $data): array
    {
        try {
            $response = $this->client->request($method, $endpoint, [
                RequestOptions::JSON => $data,
            ]);

            $body = $response->getBody()->getContents();
            $decodedResponse = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            return $decodedResponse;
        } catch (RequestException $e) {
            throw new Exception($this->extractErrorMessage($e));
        } catch (GuzzleException $e) {
            throw new Exception('Request failed: ' . $e->getMessage());
        }
    }

    /**
     * Extract error message from request exception
     */
    private function extractErrorMessage(RequestException $e): string
    {
        $response = $e->getResponse();

        if ($response === null) {
            return 'Connection failed: ' . $e->getMessage();
        }

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($data['message'])) {
            return $data['message'];
        }

        return 'Request failed with status ' . $response->getStatusCode();
    }
}
