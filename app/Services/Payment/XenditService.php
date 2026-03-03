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

    // Tambahkan parameter $items dan $fees dengan default array kosong []
    public function createInvoice(string $externalId, float $amount, string $payerEmail, string $description, array $items = [], array $fees = [])
    {
        // 1. Siapkan payload dasar
        $payload = [
            'external_id' => $externalId,
            'amount' => $amount,
            'payer_email' => $payerEmail,
            'description' => $description,
            'invoice_duration' => 3600, // Invoice valid for 1 hour
            'currency' => 'IDR',
        ];

        // 2. Tambahkan Items jika ada (Rincian Kamar)
        if (!empty($items)) {
            $payload['items'] = $items;
        }

        // 3. Tambahkan Fees jika ada (Rincian Biaya Admin)
        if (!empty($fees)) {
            $payload['fees'] = $fees;
        }

        // 4. Kirim request ke Xendit
        $response = Http::withBasicAuth($this->secretKey, '')
            ->post($this->baseUrl . '/v2/invoices', $payload);

        if ($response->failed()) {
            throw ValidationException::withMessages(
                ['payment' => 'Gagal menghubungi server pembayaran: ' . $response->json('message')]
            );
        }

        return $response->json();
    }
}