<?php

use App\Http\Controllers\DiagnosisController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DiagnosisController::class, 'create'])->name('diagnosis.create');
Route::post('/diagnosticos', [DiagnosisController::class, 'store'])->name('diagnosis.store');
Route::get('/diagnosticos/{diagnosis}', [DiagnosisController::class, 'show'])->name('diagnosis.show');
