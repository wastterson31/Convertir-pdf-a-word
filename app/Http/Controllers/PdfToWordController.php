<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;

class PdfToWordController extends Controller
{
    public function convert(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf',
        ]);

        $file = $request->file('file');

        $client = new Client();
        $response = $client->post('http://localhost:5000/convert', [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($file->getPathname(), 'r'),
                    'filename' => $file->getClientOriginalName()
                ]
            ]
        ]);

        $contentDisposition = $response->getHeader('Content-Disposition')[0];
        preg_match('/filename="(.+)"/', $contentDisposition, $matches);
        $filename = $matches[1] ?? 'output.docx';

        $folderName = now()->format('YmdHms');
        Storage::put("converted/{$folderName}/" . $filename, $response->getBody()->getContents());

        return response()->download(storage_path("app/converted/{$folderName}/" . $filename));
    }
}
