<?php

namespace App\Helpers;

use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Support\Facades\Log;

class BroadcastHelper
{
    /**
     * Safely dispatch a broadcast event, catching and logging any errors
     * without interrupting the application flow
     */
    public static function safeDispatch($event)
    {
        try {
            event($event);
        } catch (BroadcastException $e) {
            // Log the error but don't throw it
            Log::warning('Broadcast event failed (suppressed): ' . $e->getMessage(), [
                'event' => get_class($event),
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            // Catch any other broadcasting-related errors
            if (str_contains($e->getMessage(), 'Pusher') || 
                str_contains($e->getMessage(), 'cURL') ||
                str_contains($e->getMessage(), 'broadcast')) {
                Log::warning('Broadcast event failed (suppressed): ' . $e->getMessage(), [
                    'event' => get_class($event),
                    'error' => $e->getMessage()
                ]);
            } else {
                // Re-throw non-broadcasting errors
                throw $e;
            }
        }
    }
}

