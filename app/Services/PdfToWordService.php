<?php

namespace App\Services;

use Ilovepdf\Ilovepdf;
use Illuminate\Support\Facades\Log;

class PdfToWordService
{
    protected $ilovepdf;

    public function __construct()
    {
        $this->ilovepdf = new Ilovepdf(env('ILOVEPDF_PROJECT_PUBLIC_ID'), env('ILOVEPDF_PROJECT_SECRET_KEY'));
    }

    public function convert($pdfPath)
    {
        // Crear una tarea para convertir de PDF a Word
        $task = $this->ilovepdf->newTask('officepdf');

        // Añadir el archivo PDF a la tarea
        $task->addFile($pdfPath);

        // Configurar el nombre de archivo de salida
        $outputFileName = uniqid() . '.docx';
        $outputPath = public_path('converted_files/' . $outputFileName);
        $task->setOutputFilename($outputFileName);

        // Asegúrate de que la carpeta existe
        if (!file_exists(public_path('converted_files'))) {
            mkdir(public_path('converted_files'), 0777, true);
        }

        // Procesar la tarea
        try {
            $task->execute();
        } catch (\Exception $e) {
            Log::error('Error durante el procesamiento de la tarea: ' . $e->getMessage());
            throw new \Exception('Error durante el procesamiento de la tarea.');
        }

        // Descargar el archivo convertido
        try {
            $task->download(public_path('converted_files'));
        } catch (\Exception $e) {
            Log::error('Error durante la descarga: ' . $e->getMessage());
            throw new \Exception('Error durante la descarga.');
        }

        // Verificar si el archivo convertido existe y devolver la ruta
        if (!file_exists($outputPath)) {
            Log::error('El archivo descargado no existe en: ' . $outputPath);
            throw new \Exception('El archivo descargado no existe.');
        }

        return $outputPath;
    }
}
