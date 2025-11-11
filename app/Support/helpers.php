<?php

if (!function_exists('formatDate')) {
    /**
     * Format date to Indonesian format
     */
    function formatDate($date, string $format = 'd M Y'): string
    {
        if (!$date) {
            return '-';
        }

        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date->format($format);
    }
}

if (!function_exists('formatRupiah')) {
    /**
     * Format number to Rupiah currency
     */
    function formatRupiah($amount, bool $withSymbol = true): string
    {
        $formatted = number_format($amount, 0, ',', '.');

        return $withSymbol ? 'Rp ' . $formatted : $formatted;
    }
}

if (!function_exists('slugify')) {
    /**
     * Convert string to URL-friendly slug
     */
    function slugify(string $text): string
    {
        // Convert to lowercase
        $text = strtolower($text);

        // Replace spaces with hyphens
        $text = preg_replace('/\s+/', '-', $text);

        // Remove special characters
        $text = preg_replace('/[^a-z0-9\-]/', '', $text);

        // Remove multiple hyphens
        $text = preg_replace('/-+/', '-', $text);

        // Trim hyphens from start and end
        return trim($text, '-');
    }
}

if (!function_exists('randomCode')) {
    /**
     * Generate random code
     */
    function randomCode(int $length = 8, bool $numericOnly = false): string
    {
        if ($numericOnly) {
            return str_pad((string) rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
        }

        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $code;
    }
}

if (!function_exists('idEncrypt')) {
    /**
     * Encrypt ID for URL safety
     */
    function idEncrypt($id): string
    {
        return base64_encode(encrypt($id));
    }
}

if (!function_exists('idDecrypt')) {
    /**
     * Decrypt encrypted ID
     */
    function idDecrypt(string $encrypted): mixed
    {
        try {
            return decrypt(base64_decode($encrypted));
        } catch (\Exception $e) {
            return null;
        }
    }
}

if (!function_exists('isActiveNav')) {
    /**
     * Check if navigation is active
     */
    function isActiveNav(string $route, string $class = 'active'): string
    {
        return request()->routeIs($route) ? $class : '';
    }
}

if (!function_exists('asset_versioned')) {
    /**
     * Get asset with version for cache busting
     */
    function asset_versioned(string $path): string
    {
        $version = config('app.version', '1.0.0');
        $separator = str_contains($path, '?') ? '&' : '?';

        return asset($path) . $separator . 'v=' . $version;
    }
}

