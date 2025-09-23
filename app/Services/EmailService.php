<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    public function sendCriticalAlert($to, $subject, $message, $data = [])
    {
        try {
            Mail::raw($message, function ($mail) use ($to, $subject) {
                $mail->to($to)->subject($subject);
            });
            
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