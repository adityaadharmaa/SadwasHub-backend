<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class XenditService
{
    protected $secretKey;
    protected $baseUrl = 'https://api.xendit.co';
    public function __construct()
    {
        $this->secretKey = env('XENDIT_SECRET_KEY');
    }

    public function createInvoice(string $externalId, float $amount, string $payerEmail, string $description)
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->post($this->baseUrl . '/v2/invoices', [
                'external_id' => $externalId,
                'amount' => $amount,
                'payer_email' => $payerEmail,
                'description' => $description,
                'invoice_duration' => 3600, // Invoice valid for 1 hour
                'currency' => 'IDR',
            ]);

        if ($response->failed()) {
            throw ValidationException::withMessages(
                ['payment' => 'Gagal menghubungi server pembayaran: ' . $response->json('message')]
            );
        }

        return $response->json();
    }
}
