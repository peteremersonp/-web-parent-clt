<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    /**
     * Ubicaciones posibles del instalador, en orden de prioridad:
     * 1. storage/app/private/  -> producción (el exe se sube/copia aquí).
     * 2. Salida de Inno Setup en el host Windows (vía /mnt/c) -> desarrollo:
     *    sirve SIEMPRE la última compilación sin copiar nada.
     */
    private const CANDIDATES = [
        'storage/app/private/ParentCLT-Setup.exe',
        '/mnt/c/Users/Unicomfacauca/parent-clt/ParentCLT/Installer/Output/ParentCLT-Setup.exe',
    ];

    public function setup(): BinaryFileResponse|JsonResponse
    {
        foreach (self::CANDIDATES as $candidate) {
            $path = base_path($candidate);

            if (is_file($path) && filesize($path) > 0) {
                return response()->download($path, 'ParentCLT-Setup.exe', [
                    'Content-Type' => 'application/octet-stream',
                ]);
            }
        }

        abort(404, 'Instalador no disponible');
    }
}
