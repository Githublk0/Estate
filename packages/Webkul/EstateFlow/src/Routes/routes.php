<?php

use Illuminate\Support\Facades\Route;
use Webkul\EstateFlow\Http\Controllers\Admin\PropertyController;
use Webkul\EstateFlow\Http\Controllers\Admin\QualificationHistoryController;
use Webkul\EstateFlow\Http\Controllers\QualificationController;

Route::middleware(['web', 'user'])
    ->prefix('api/estateflow')
    ->group(function () {
        Route::post('/qualify-lead', QualificationController::class)
            ->name('estateflow.qualify-lead');
    });

Route::middleware(['web', 'admin_locale', 'user'])
    ->prefix(config('app.admin_path'))
    ->name('admin.')
    ->group(function () {
        Route::prefix('estateflow')
            ->name('estateflow.')
            ->group(function () {
                Route::get('properties', [PropertyController::class, 'index'])
                    ->name('properties.index');
                Route::get('properties/create', [PropertyController::class, 'create'])
                    ->name('properties.create');
                Route::post('properties', [PropertyController::class, 'store'])
                    ->name('properties.store');
                Route::get('properties/{id}', [PropertyController::class, 'show'])
                    ->name('properties.show');
                Route::get('properties/{id}/edit', [PropertyController::class, 'edit'])
                    ->name('properties.edit');
                Route::put('properties/{id}', [PropertyController::class, 'update'])
                    ->name('properties.update');
                Route::patch('properties/{id}/status', [PropertyController::class, 'toggleStatus'])
                    ->name('properties.status');
                Route::delete('properties/{id}', [PropertyController::class, 'destroy'])
                    ->name('properties.destroy');

                Route::get('qualifications', QualificationHistoryController::class)
                    ->name('qualifications.index');
            });
    });
