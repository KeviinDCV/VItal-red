<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SMSService
{
    private $apiUrl = 'https://api.twilio.com/2010-04-01/Accounts/';
    private $accountSid;
    private $authToken;
    private $fromNumber;

    public function __construct()
    {
        $this->accountSid = config('services.twilio.sid');
        $this->authToken = config('services.twilio.token');
        $this->fromNumber = config('services.twilio.from');
    }

    public function sendUrgentSMS($to, $message)
    {
        try {
            // Simulación para desarrollo local
            if (app()->environment('local')) {
                Log::info('SMS simulado enviado', ['to' => $to, 'message' => $message]);
                return true;
            }

            $response = Http::withBasicAuth($this->accountSid, $this->authToken)
                ->asForm()
                ->post($this->apiUrl . $this->accountSid . '/Messages.json', [
                    'From' => $this->fromNumber,
                    'To' => $to,
                    'Body' => $message
                ]);

            Log::info('SMS enviado', ['to' => $to, 'status' => $response->status()]);
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Error enviando SMS', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function sendBulkSMS($recipients, $message)
    {
        $sent = 0;
        foreach ($recipients as $phone) {
            if ($this->sendUrgentSMS($phone, $message)) {
                $sent++;
            }
        }
        return $sent;
    }
}