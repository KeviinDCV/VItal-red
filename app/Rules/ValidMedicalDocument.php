<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidMedicalDocument implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value) {
            return;
        }

        $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $extension = strtolower($value->getClientOriginalExtension());
        
        if (!in_array($extension, $allowedExtensions)) {
            $fail('El archivo debe ser un documento médico válido (PDF, DOC, DOCX, JPG, PNG).');
            return;
        }

        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($value->getSize() > $maxSize) {
            $fail('El archivo no debe superar los 10MB.');
            return;
        }

        // Validar que no sea un archivo ejecutable
        $dangerousExtensions = ['exe', 'bat', 'cmd', 'scr', 'pif', 'vbs', 'js'];
        if (in_array($extension, $dangerousExtensions)) {
            $fail('Tipo de archivo no permitido por seguridad.');
        }
    }
}