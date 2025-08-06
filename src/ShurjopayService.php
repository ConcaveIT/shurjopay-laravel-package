<?php

namespace dits\ShurjopayLaravelPackage;
use Dits\ShurjopayLaravelPackage\Http\Client;
use Illuminate\Support\Facades\Http;

class ShurjopayService
{
    
    
    protected $client;
    protected $baseUrl;
    protected $merchant_username;
    protected $merchant_password;
    protected $token;
    protected $store_id;

    public function __construct()
    {
        $this->merchant_username = config('shurjopay.merchant_username');
        $this->merchant_password = config('shurjopay.merchant_password');
        $this->baseUrl = config('shurjopay.server_url');
        $this->client = new Client();
    }



    /**
     * Generate token
     */
    public function getToken(): array
    {
        $tokenResponse = $this->client->postJson($this->baseUrl . '/api/get_token', [
            'username' =>  $this->merchant_username,
            'password' => $this->merchant_password,
        ]);

        if (empty($tokenResponse['token'])) {
            throw new \RuntimeException('Unable to get shurjoPay token: ' . json_encode($tokenResponse));
        }

        $this->token = $tokenResponse['token'];
        $this->store_id = $tokenResponse['store_id'];
        return $tokenResponse;
    }


    /**
     * Initiates a payment with the provided data.
     *
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    // This method should match the interface method signature
    // and should handle the minimal input you mentioned.
    // It should also handle the required fields and return the expected response.
     // If you need to handle more complex logic, consider breaking it down into smaller methods.
     // This is a basic implementation; you can expand it based on your requirements.
     // Ensure you have the necessary error handling and validation as per your application's needs.
     // This method should return the response from the Shurjopay API.
     // You can also implement additional methods for verifying transactions, generating transaction IDs, etc.
     // Make sure to include any necessary headers or authentication as required by the Shurjopay API.
     // If you need to handle different payment options, you can add logic to process           
    public function sendPayment(array $data)
    {

        // Basic validation for the minimal input you said you'll provide
        if (empty($data['amount'])) {
            throw new \InvalidArgumentException('amount is required');
        }
        if (empty($data['transaction_id'])) {
            throw new \InvalidArgumentException('transaction_id is required');
        }
        if (empty($data['return_url'])) {
            throw new \InvalidArgumentException('return_url is required');
        }
        if (empty($data['cancel_url'])) {
            throw new \InvalidArgumentException('cancel_url is required');
        }

        if (empty($this->token)) {
            $this->getToken();
        }


        // Build default values from config
        $prefix     = config('shurjopay.prefix', 'sp');
        $currency   = $data['currency'] ?? config('shurjopay.currency', 'BDT');


        // client IP from current HTTP request if available (null otherwise)
        $clientIp = request()->ip() ?? $data['client_ip'] ?? null;
    
        // Build the payload array that matches shurjoPay fields
        $payload = [
            // Mandatory fields (as per shurjoPay docs)
            'prefix'                => $prefix,
            'token'                 => $this->token,
            'return_url'            => $data['return_url'],
            'cancel_url'            => $data['cancel_url'],
            'store_id'              => $this->store_id,
            'amount'                => (float) $data['amount'],
            'order_id'              => (string) $data['transaction_id'],
            'currency'              => (string) $currency,
            'customer_name'         => $data['customer_name'] ??  'Customer',
            'customer_address'      => $data['customer_address'] ?? 'Dhaka',
            'customer_email'        => $data['customer_email'] ?? 'customer@example.com',
            'customer_phone'        => $data['customer_phone'] ?? '01711111111',
            'customer_city'         => $data['customer_city'] ?? 'Dhaka',
            'customer_post_code'    => $data['customer_post_code'] ?? '1212',
            'client_ip'             => $clientIp ?? '',
            // Optional fields (empty/defaults)
            'discount_amount'       => isset($data['discount_amount']) ? (float) $data['discount_amount'] : 0.0,
            'disc_percent'          => isset($data['disc_percent']) ? (float) $data['disc_percent'] : 0.0,
            'customer_state'        => $data['customer_state'] ?? '',
            'customer_country'      => $data['customer_country'] ?? '',
            'shipping_address'      => $data['shipping_address'] ?? '',
            'shipping_city'         => $data['shipping_city'] ?? '',
            'shipping_country'      => $data['shipping_country'] ?? '',
            'received_person_name'  => $data['received_person_name'] ?? '',
            'shipping_phone_number' => $data['shipping_phone_number'] ?? '',
            'value1'                => $data['value1'] ?? '',
            'value2'                => $data['value2'] ?? '',
            'value3'                => $data['value3'] ?? '',
            'value4'                => $data['value4'] ?? '',
        ];

    $response = Http::withToken($this->token)
        ->asMultipart()
        ->post($this->baseUrl . '/api/secret-pay', $payload);

        // Check if the response is successful
        if (!$response->successful()) {
            // Handle error response
            return [
                'status' => 'error',
                'message' => $response->body(),
                'http_status' => $response->status(),
            ];
        }   

        $body = $response->json();
        if ($body === null) {
            // fallback: return raw body for debugging
            return [
                'status' => 'invalid_json',
                'raw' => (string) $response->getBody(),
                'http_status' => $response->getStatusCode(),
            ];
        }

       // there is checkout_url return checkout_url
        if (isset($body['checkout_url']) && !empty($body['checkout_url'])) {
            // Return the checkout URL for redirection
            return [
                'status' => 'success',
                'checkout_url' => $body['checkout_url'],
                'http_status' => $response->getStatusCode(),
            ];
        }           
        // If no checkout_url, return the body as is
        return [
            'status' => 'error',
            'message' => $body,
            'http_status' => $response->getStatusCode(),
        ];
    }

    public function verifyTransaction($transactionId)
    {
        $response = $this->client->get("{$this->baseUrl}/payment/verify/{$transactionId}", [
            'headers' => $this->getHeaders(),
        ]);

        return json_decode($response->getBody(), true);
    }

    protected function getHeaders()
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json',
        ];
    }



}
