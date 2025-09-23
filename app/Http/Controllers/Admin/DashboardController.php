<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistroMedico;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Mostrar el dashboard del administrador con todos los registros médicos
     */
    public function index(Request $request)
    {
        $request->validate(['search' => 'nullable|string|max:255']);
        $search = $request->get('search');

        $registros = RegistroMedico::with('user')
            ->when($search, function ($query, $search) {
                $sanitizedSearch = '%' . addslashes($search) . '%';
                return $query->where(function ($q) use ($sanitizedSearch) {
                    $q->where('nombre', 'like', $sanitizedSearch)
                      ->orWhere('apellidos', 'like', $sanitizedSearch)
                      ->orWhere('numero_identificacion', 'like', $sanitizedSearch)
                      ->orWhere('diagnostico_principal', 'like', $sanitizedSearch);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('dashboard', [
            'registros' => $registros,
            'search' => $search,
        ]);
    }

    /**
     * Buscar registros médicos (para AJAX)
     */
    public function buscarRegistros(Request $request)
    {
        $request->validate(['search' => 'nullable|string|max:255']);
        $search = $request->get('search');

        $registros = RegistroMedico::with('user')
            ->when($search, function ($query, $search) {
                $sanitizedSearch = '%' . addslashes($search) . '%';
                return $query->where(function ($q) use ($sanitizedSearch) {
                    $q->where('nombre', 'like', $sanitizedSearch)
                      ->orWhere('apellidos', 'like', $sanitizedSearch)
                      ->orWhere('numero_identificacion', 'like', $sanitizedSearch)
                      ->orWhere('diagnostico_principal', 'like', $sanitizedSearch);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return response()->json($registros);
    }

    /**
     * Descargar historia clínica (administrador puede descargar cualquier historia)
     */
    public function descargarHistoria(RegistroMedico $registro)
    {
        // Verificar que existe el archivo
        if (!$registro->historia_clinica_path) {
            abort(404, 'No hay historia clínica adjunta para este registro.');
        }

        $filePath = storage_path('app/public/' . $registro->historia_clinica_path);

        // Verificar que el archivo existe físicamente
        if (!file_exists($filePath)) {
            abort(404, 'El archivo de historia clínica no se encuentra en el servidor.');
        }

        // Obtener información del archivo
        $fileName = 'historia_clinica_' . $registro->numero_identificacion . '_' . $registro->nombre . '_' . $registro->apellidos;
        $fileExtension = pathinfo($registro->historia_clinica_path, PATHINFO_EXTENSION);
        $downloadName = $fileName . '.' . $fileExtension;

        // Retornar el archivo para descarga
        return response()->download($filePath, $downloadName, [
            'Content-Type' => mime_content_type($filePath),
        ]);
    }
}
