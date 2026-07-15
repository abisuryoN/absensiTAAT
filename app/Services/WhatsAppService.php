<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message to a recipient.
     */
    public function send(string $phone, string $message, ?int $studentId = null, ?int $parentId = null, string $type = 'attendance_gate'): Notification
    {
        // 1. Create a pending notification log entry
        $notification = Notification::create([
            'student_id' => $studentId,
            'parent_id' => $parentId,
            'type' => $type,
            'channel' => 'whatsapp',
            'phone_number' => $phone,
            'message' => $message,
            'status' => 'pending',
            'attempts' => 0,
        ]);

        return $this->sendNotification($notification);
    }

    /**
     * Execute sending an existing notification record.
     */
    public function sendNotification(Notification $notification): Notification
    {
        $notification->increment('attempts');
        
        $enabled = Setting::getVal('whatsapp_enabled', false);
        if (!$enabled) {
            $notification->update([
                'status' => 'failed',
                'response' => 'WhatsApp dispatch disabled in settings.',
            ]);
            return $notification;
        }

        $provider = Setting::getVal('whatsapp_provider', 'fonnte');
        $apiUrl = Setting::getVal('whatsapp_api_url', 'https://api.fonnte.com/send');
        $token = Setting::getVal('whatsapp_api_token', '');
        $device = Setting::getVal('whatsapp_sender_device', '');

        try {
            $response = null;

            switch ($provider) {
                case 'fonnte':
                    $response = Http::withHeaders([
                        'Authorization' => $token,
                    ])->post($apiUrl, [
                        'target' => $notification->phone_number,
                        'message' => $notification->message,
                        'countryCode' => '62',
                    ]);
                    break;

                case 'wablas':
                    // WABlas sending API structure
                    $response = Http::withHeaders([
                        'Authorization' => $token,
                    ])->post($apiUrl, [
                        'phone' => $notification->phone_number,
                        'message' => $notification->message,
                    ]);
                    break;

                case 'woowa':
                    // Woowa sending API structure
                    $response = Http::post($apiUrl, [
                        'key' => $token,
                        'phone' => $notification->phone_number,
                        'message' => $notification->message,
                    ]);
                    break;

                default:
                    throw new \Exception("WhatsApp provider '{$provider}' tidak dikenal.");
            }

            if ($response && $response->successful()) {
                $notification->update([
                    'status' => 'sent',
                    'response' => $response->body(),
                    'sent_at' => now(),
                ]);
            } else {
                $notification->update([
                    'status' => 'failed',
                    'response' => $response ? $response->body() : 'No response from gateway.',
                ]);
            }
        } catch (\Exception $e) {
            Log::error("WhatsApp Gateway Send Failure: " . $e->getMessage());
            $notification->update([
                'status' => 'failed',
                'response' => $e->getMessage(),
            ]);
        }

        return $notification;
    }
}
