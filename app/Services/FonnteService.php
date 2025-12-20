<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class FonnteService
{
    protected $apiUrl;
    protected $apiKey;
    protected $delayBetweenMessages; // Delay dalam detik untuk anti ban

    public function __construct()
    {
        $this->apiUrl = config('services.fonnte.url', 'https://api.fonnte.com');
        $this->apiKey = config('services.fonnte.api_key');
        $this->delayBetweenMessages = (int) config('services.fonnte.delay_between_messages', 3); // Default 3 detik
    }

    /**
     * Send WhatsApp message via Fonnte API
     *
     * @param string $phone Phone number (format: 6281234567890)
     * @param string $message Message content
     * @return array
     */
    public function sendMessage(string $phone, string $message): array
    {
        // Format phone number (remove +, spaces, dashes)
        $phone = $this->formatPhoneNumber($phone);

        if (!$phone) {
            return [
                'success' => false,
                'message' => 'Nomor telepon tidak valid'
            ];
        }

        // Anti ban: Check rate limit
        if (!$this->checkRateLimit($phone)) {
            return [
                'success' => false,
                'message' => 'Terlalu banyak pesan dalam waktu singkat. Silakan tunggu beberapa saat.'
            ];
        }

        // Anti ban: Add delay between messages
        $this->addDelay();

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => $this->apiKey,
                ])
                ->post("{$this->apiUrl}/send", [
                    'target' => $phone,
                    'message' => $message,
                    'countryCode' => '62', // Indonesia
                ]);

            $result = $response->json();

            // Mark rate limit
            $this->markRateLimit($phone);

            // Fonnte API returns status: true (boolean) when successful
            // Also check for "success! message in queue" in detail
            $isSuccess = false;
            if ($response->successful()) {
                // Check if status is true (boolean) or 'success' (string)
                if (isset($result['status'])) {
                    $isSuccess = ($result['status'] === true || $result['status'] === 'success');
                }
                // Also check detail message for queue success
                if (!$isSuccess && isset($result['detail'])) {
                    $isSuccess = (stripos($result['detail'], 'success') !== false && 
                                 stripos($result['detail'], 'queue') !== false);
                }
            }

            if ($isSuccess) {
                return [
                    'success' => true,
                    'message' => $result['detail'] ?? 'Pesan berhasil dikirim',
                    'data' => $result
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? ($result['detail'] ?? 'Gagal mengirim pesan'),
                'data' => $result
            ];

        } catch (\Exception $e) {
            Log::error('Fonnte API Error: ' . $e->getMessage(), [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number to Fonnte format
     *
     * @param string $phone
     * @return string|null
     */
    protected function formatPhoneNumber(string $phone): ?string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Remove leading +
        $phone = ltrim($phone, '+');

        // If starts with 0, replace with 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // If doesn't start with 62, add it
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        // Validate length (should be 11-15 digits after 62)
        if (strlen($phone) < 11 || strlen($phone) > 15) {
            return null;
        }

        return $phone;
    }

    /**
     * Check rate limit untuk anti ban
     *
     * @param string $phone
     * @return bool
     */
    protected function checkRateLimit(string $phone): bool
    {
        $key = "fonnte_rate_limit_{$phone}";
        $count = Cache::get($key, 0);

        // Max 10 pesan per 5 menit per nomor (lebih longgar untuk testing)
        // Atau bisa diatur via config
        $maxMessages = (int) config('services.fonnte.max_messages_per_period', 10);
        $periodMinutes = (int) config('services.fonnte.rate_limit_period', 5);

        if ($count >= $maxMessages) {
            Log::warning("Rate limit exceeded for phone: {$phone}", [
                'count' => $count,
                'max' => $maxMessages,
                'period' => $periodMinutes
            ]);
            return false;
        }

        return true;
    }

    /**
     * Mark rate limit
     *
     * @param string $phone
     * @return void
     */
    protected function markRateLimit(string $phone): void
    {
        $key = "fonnte_rate_limit_{$phone}";
        $count = Cache::get($key, 0);
        $periodMinutes = (int) config('services.fonnte.rate_limit_period', 5);
        Cache::put($key, $count + 1, now()->addMinutes($periodMinutes));
    }

    /**
     * Add delay between messages untuk anti ban
     *
     * @return void
     */
    protected function addDelay(): void
    {
        $lastSent = Cache::get('fonnte_last_sent', 0);
        $now = time();
        $timeSinceLastSent = $now - $lastSent;

        if ($timeSinceLastSent < $this->delayBetweenMessages) {
            $waitTime = $this->delayBetweenMessages - $timeSinceLastSent;
            sleep($waitTime);
        }

        Cache::put('fonnte_last_sent', time(), now()->addMinutes(1));
    }

    /**
     * Send template message (jika menggunakan template)
     *
     * @param string $phone
     * @param string $templateName
     * @param array $parameters
     * @return array
     */
    public function sendTemplate(string $phone, string $templateName, array $parameters = []): array
    {
        $phone = $this->formatPhoneNumber($phone);

        if (!$phone) {
            return [
                'success' => false,
                'message' => 'Nomor telepon tidak valid'
            ];
        }

        // Anti ban checks
        if (!$this->checkRateLimit($phone)) {
            return [
                'success' => false,
                'message' => 'Terlalu banyak pesan dalam waktu singkat. Silakan tunggu beberapa saat.'
            ];
        }

        $this->addDelay();

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => $this->apiKey,
                ])
                ->post("{$this->apiUrl}/send", [
                    'target' => $phone,
                    'message' => $templateName,
                    'template' => true,
                    'parameters' => $parameters,
                    'delay' => rand(1, 3),
                ]);

            $result = $response->json();
            $this->markRateLimit($phone);

            // Fonnte API returns status: true (boolean) when successful
            $isSuccess = false;
            if ($response->successful()) {
                if (isset($result['status'])) {
                    $isSuccess = ($result['status'] === true || $result['status'] === 'success');
                }
                if (!$isSuccess && isset($result['detail'])) {
                    $isSuccess = (stripos($result['detail'], 'success') !== false && 
                                 stripos($result['detail'], 'queue') !== false);
                }
            }

            if ($isSuccess) {
                return [
                    'success' => true,
                    'message' => $result['detail'] ?? 'Pesan template berhasil dikirim',
                    'data' => $result
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? ($result['detail'] ?? 'Gagal mengirim pesan template'),
                'data' => $result
            ];

        } catch (\Exception $e) {
            Log::error('Fonnte Template API Error: ' . $e->getMessage(), [
                'phone' => $phone,
                'template' => $templateName,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}

