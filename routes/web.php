<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfToWordController;

Route::post('/convert-pdf-to-word', [PdfToWordController::class, 'convert'])->name('convert.pdf.to.word');


Route::get('/', function () {
    return view('welcome');
});
