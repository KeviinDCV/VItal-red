<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CRUD TEST SIMPLIFICADO ===\n";

// 1. USERS - FUNCIONA ✅
echo "1. USERS: ";
$u = App\Models\User::create(['name'=>'T','email'=>'t'.time().'@t.com','password'=>bcrypt('p'),'role'=>'medico','is_active'=>1,'telefono'=>'+57']);
$u->update(['name'=>'Updated']);
$u->delete();
echo "OK\n";

// 2. NOTIFICACIONES - SIMPLE
echo "2. NOTIFICACIONES: ";
$n = App\Models\Notificacion::create(['user_id'=>1,'tipo'=>'test','titulo'=>'Test','mensaje'=>'Test','prioridad'=>'media']);
$n->update(['titulo'=>'Updated']);
$n->delete();
echo "OK\n";

// 3. IPS - USANDO CAMPOS MÍNIMOS
echo "3. IPS: ";
$i = App\Models\IPS::create(['codigo_prestador'=>'T'.time(),'nombre'=>'Test','nit'=>'123-1','tipo_ips'=>'HOSPITAL','departamento'=>'Test','municipio'=>'Test','direccion'=>'Test','telefono'=>'123','email'=>'t@t.com','activa'=>1,'acepta_referencias'=>1,'capacidad_diaria'=>50,'fecha_registro'=>now()]);
$i->update(['nombre'=>'Updated']);
$i->delete();
echo "OK\n";

// 4. EVENTOS AUDITORIA
echo "4. AUDITORIA: ";
$a = App\Models\EventoAuditoria::create(['user_id'=>1,'evento'=>'test','descripcion'=>'Test','ip_address'=>'127.0.0.1','user_agent'=>'Test']);
$a->update(['descripcion'=>'Updated']);
$a->delete();
echo "OK\n";

// 5. VERIFICAR LECTURA DE DATOS EXISTENTES
echo "5. LECTURA DATOS: ";
echo "Users:" . App\Models\User::count() . " ";
echo "Solicitudes:" . App\Models\SolicitudReferencia::count() . " ";
echo "Registros:" . App\Models\RegistroMedico::count() . " ";
echo "Notificaciones:" . App\Models\Notificacion::count() . " ";
echo "IPS:" . App\Models\IPS::count();
echo " OK\n";

echo "\n=== CRUD BÁSICO FUNCIONAL ===\n";