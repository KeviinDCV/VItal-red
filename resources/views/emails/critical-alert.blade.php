<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>🚨 Alerta Crítica - VItal-red</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #dc2626; color: white; padding: 20px; text-align: center; }
        .content { padding: 30px; }
        .alert-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 15px; margin: 20px 0; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; }
        .btn { display: inline-block; background: #dc2626; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚨 ALERTA CRÍTICA</h1>
            <p>Sistema VItal-red</p>
        </div>
        
        <div class="content">
            <h2>{{ $title }}</h2>
            
            <div class="alert-box">
                <strong>⚠️ Atención Inmediata Requerida</strong>
                <p>{{ $message }}</p>
            </div>
            
            @if(!empty($data))
            <h3>Detalles:</h3>
            <ul>
                @foreach($data as $key => $value)
                <li><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</li>
                @endforeach
            </ul>
            @endif
            
            <p><strong>Fecha y Hora:</strong> {{ $timestamp->format('d/m/Y H:i:s') }}</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.url') }}" class="btn">Acceder al Sistema</a>
            </div>
            
            <p><em>Esta es una alerta automática del sistema VItal-red. Por favor, tome las acciones necesarias de inmediato.</em></p>
        </div>
        
        <div class="footer">
            <p>VItal-red - Sistema de Referencias Médicas</p>
            <p>Este email fue enviado automáticamente. No responder.</p>
        </div>
    </div>
</body>
</html>