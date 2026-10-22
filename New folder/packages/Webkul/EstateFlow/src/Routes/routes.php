<?php
use Illuminate\Support\Facades\Route; use Webkul\EstateFlow\Http\Controllers\QualificationController;
Route::middleware(['web'])->prefix('api/estateflow')->group(function(){ Route::post('/qualify-lead',QualificationController::class)->name('estateflow.qualify-lead'); });
