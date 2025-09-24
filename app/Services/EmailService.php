<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\CriticalAlertMail;

class EmailService
{
    public function sendCriticalAlert($to, $subject, $message, $data = [])
    {
        try {
            Mail::to($to)->send(new CriticalAlertMail($subject, $message, $data));
            
            Log::info('Email crítico enviado', ['to' => $to, 'subject' => $subject]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error enviando email', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function sendBulkNotification($recipients, $subject, $message)
    {
        $sent = 0;
        foreach ($recipients as $email) {
            if ($this->sendCriticalAlert($email, $subject, $message)) {
                $sent++;
            }
        }
        return $sent;
    }
}