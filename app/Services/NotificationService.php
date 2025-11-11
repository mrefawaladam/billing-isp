<?php

namespace App\Services;

class NotificationService
{
    /**
     * Get notification data for HTMX trigger
     */
    public static function success(string $message): array
    {
        return [
            'type' => 'success',
            'message' => $message
        ];
    }

    /**
     * Get error notification data
     */
    public static function error(string $message): array
    {
        return [
            'type' => 'error',
            'message' => $message
        ];
    }

    /**
     * Get warning notification data
     */
    public static function warning(string $message): array
    {
        return [
            'type' => 'warning',
            'message' => $message
        ];
    }

    /**
     * Get info notification data
     */
    public static function info(string $message): array
    {
        return [
            'type' => 'info',
            'message' => $message
        ];
    }
}

