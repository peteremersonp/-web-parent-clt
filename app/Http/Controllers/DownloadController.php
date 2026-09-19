<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    /**
     * Ruta de salida de ISCC en el host Windows (accesible desde WSL via /mnt/c).
     * Sirve SIEMPRE el ultimo instalador compilado, sin necesidad de copiarlo.
     */
    private const SETUP_PATH = '/mnt/c/Users/Unicomfacauca/parent-clt/ParentCLT/Installer/Output/ParentCLT-Setup.exe';

    public function setup(): BinaryFileResponse|JsonResponse
    {
        if (! is_file(self::SETUP_PATH)) {
            abort(404, 'Instalador no disponible');
        }

        return response()->download(self::SETUP_PATH, 'ParentCLT-Setup.exe', [
            'Content-Type' => 'application/octet-stream',
        ]);
    }
}
