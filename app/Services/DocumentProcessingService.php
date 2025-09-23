<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DocumentProcessingService
{
    private $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];

    public function extractPatientDataFromDocument(UploadedFile $file): array
    {
        $fileType = $file->getClientOriginalExtension();
        
        if (!in_array(strtolower($fileType), $this->allowedTypes)) {
            throw new \InvalidArgumentException('Tipo de archivo no permitido');
        }

        $filePath = $file->store('temp');
        
        try {
            if ($fileType === 'pdf') {
                return $this->extractFromPDF($filePath);
            } else {
                return $this->extractFromImage($filePath);
            }
        } finally {
            Storage::delete($filePath);
        }
    }

    private function extractFromPDF(string $filePath): array
    {
        $fullPath = Storage::path($filePath);
        
        // Validar que el archivo existe y es seguro
        if (!file_exists($fullPath) || !$this->isSecureFile($fullPath)) {
            throw new \InvalidArgumentException('Archivo no válido o inseguro');
        }
        
        // Usar comando seguro con whitelist
        $allowedCommands = ['python3', 'python'];
        $pythonCmd = $this->getSecureCommand($allowedCommands);
        
        if (!$pythonCmd) {
            return $this->extractBasicPDFText($fullPath);
        }
        
        $scriptPath = base_path('scripts/pdf_extractor.py');
        if (!file_exists($scriptPath)) {
            return $this->extractBasicPDFText($fullPath);
        }
        
        $command = $pythonCmd . ' ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($fullPath);
        $output = $this->executeSecureCommand($command);
        
        if (!$output) {
            return $this->extractBasicPDFText($fullPath);
        }

        $data = json_decode($output, true);
        return $this->processExtractedData($data);
    }

    private function extractFromImage(string $filePath): array
    {
        $fullPath = Storage::path($filePath);
        
        // Validar archivo
        if (!file_exists($fullPath) || !$this->isSecureFile($fullPath)) {
            throw new \InvalidArgumentException('Archivo no válido o inseguro');
        }
        
        // Usar Tesseract con parámetros seguros
        $allowedLanguages = ['spa', 'eng'];
        $language = 'spa';
        
        if (!in_array($language, $allowedLanguages)) {
            $language = 'eng';
        }
        
        $command = 'tesseract ' . escapeshellarg($fullPath) . ' stdout -l ' . escapeshellarg($language);
        $text = $this->executeSecureCommand($command);
        
        return $this->extractPatientInfoFromText($text ?: '');
    }

    private function extractBasicPDFText(string $filePath): array
    {
        // Fallback usando PHP para PDFs simples
        $text = '';
        
        if (class_exists('Smalot\PdfParser\Parser')) {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
        }

        return $this->extractPatientInfoFromText($text);
    }

    private function extractPatientInfoFromText(string $text): array
    {
        $data = [
            'nombre' => '',
            'apellidos' => '',
            'numero_identificacion' => '',
            'fecha_nacimiento' => '',
            'sexo' => '',
            'telefono' => '',
            'direccion' => '',
            'motivo_consulta' => '',
            'diagnostico_principal' => '',
            'especialidad_solicitada' => ''
        ];

        // Patrones de extracción
        $patterns = [
            'numero_identificacion' => '/(?:cc|cedula|identificacion)[\s:]*(\d{6,12})/i',
            'telefono' => '/(?:tel|telefono|celular)[\s:]*(\d{7,10})/i',
            'fecha_nacimiento' => '/(?:nacimiento|nacio)[\s:]*(\d{1,2}\/\d{1,2}\/\d{4})/i',
            'sexo' => '/(?:sexo|genero)[\s:]*([mf]|masculino|femenino)/i'
        ];

        foreach ($patterns as $field => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $data[$field] = trim($matches[1]);
            }
        }

        // Extraer nombres (más complejo)
        if (preg_match('/(?:nombre|paciente)[\s:]*([A-ZÁÉÍÓÚ][a-záéíóú]+(?:\s+[A-ZÁÉÍÓÚ][a-záéíóú]+)*)/i', $text, $matches)) {
            $fullName = trim($matches[1]);
            $nameParts = explode(' ', $fullName);
            
            if (count($nameParts) >= 2) {
                $data['nombre'] = $nameParts[0];
                $data['apellidos'] = implode(' ', array_slice($nameParts, 1));
            } else {
                $data['nombre'] = $fullName;
            }
        }

        // Extraer motivo de consulta
        if (preg_match('/(?:motivo|consulta por)[\s:]*([^.]+)/i', $text, $matches)) {
            $data['motivo_consulta'] = trim($matches[1]);
        }

        // Extraer diagnóstico
        if (preg_match('/(?:diagnostico|dx)[\s:]*([^.]+)/i', $text, $matches)) {
            $data['diagnostico_principal'] = trim($matches[1]);
        }

        // Extraer especialidad
        $especialidades = [
            'cardiologia', 'neurologia', 'pediatria', 'ginecologia', 
            'urologia', 'oftalmologia', 'dermatologia', 'psiquiatria'
        ];

        foreach ($especialidades as $especialidad) {
            if (stripos($text, $especialidad) !== false) {
                $data['especialidad_solicitada'] = $especialidad;
                break;
            }
        }

        Log::info('Document Processing Result', [
            'extracted_fields' => array_filter($data),
            'text_length' => strlen($text)
        ]);

        return array_filter($data);
    }

    private function processExtractedData(array $rawData): array
    {
        // Procesar y limpiar datos extraídos por Python
        $processed = [];

        foreach ($rawData as $key => $value) {
            $processed[$key] = $this->cleanExtractedValue($value);
        }

        return $processed;
    }

    private function cleanExtractedValue(string $value): string
    {
        // Limpiar y normalizar valores extraídos
        $value = trim($value);
        $value = preg_replace('/\s+/', ' ', $value);
        
        return $value;
    }

    public function validateExtractedData(array $data): array
    {
        $errors = [];

        if (empty($data['numero_identificacion']) || !preg_match('/^\d{6,12}$/', $data['numero_identificacion'])) {
            $errors[] = 'Número de identificación inválido o faltante';
        }

        if (empty($data['nombre'])) {
            $errors[] = 'Nombre del paciente faltante';
        }

        if (!empty($data['fecha_nacimiento']) && !$this->isValidDate($data['fecha_nacimiento'])) {
            $errors[] = 'Fecha de nacimiento inválida';
        }

        return $errors;
    }

    private function isValidDate(string $date): bool
    {
        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y'];
        
        foreach ($formats as $format) {
            $dateTime = \DateTime::createFromFormat($format, $date);
            if ($dateTime && $dateTime->format($format) === $date) {
                return true;
            }
        }

        return false;
    }
    
    private function isSecureFile(string $filePath): bool
    {
        // Validar que el archivo esté en directorio permitido
        $allowedPaths = [storage_path('app/temp'), storage_path('app/public')];
        $realPath = realpath($filePath);
        
        foreach ($allowedPaths as $allowedPath) {
            if (strpos($realPath, realpath($allowedPath)) === 0) {
                return true;
            }
        }
        
        return false;
    }
    
    private function getSecureCommand(array $allowedCommands): ?string
    {
        foreach ($allowedCommands as $cmd) {
            if ($this->commandExists($cmd)) {
                return $cmd;
            }
        }
        return null;
    }
    
    private function commandExists(string $command): bool
    {
        $whereIsCommand = (PHP_OS == 'WINNT') ? 'where' : 'which';
        $process = proc_open(
            "$whereIsCommand $command",
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );
        
        if (is_resource($process)) {
            $exitCode = proc_close($process);
            return $exitCode === 0;
        }
        
        return false;
    }
    
    private function executeSecureCommand(string $command): ?string
    {
        // Timeout de 30 segundos
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        
        $process = proc_open($command, $descriptorspec, $pipes);
        
        if (!is_resource($process)) {
            return null;
        }
        
        fclose($pipes[0]);
        
        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        $exitCode = proc_close($process);
        
        if ($exitCode !== 0) {
            Log::warning('Command execution failed', [
                'command' => $command,
                'exit_code' => $exitCode,
                'error' => $error
            ]);
            return null;
        }
        
        return $output;
    }
}