<?php

use Illuminate\Support\Facades\Route;
use Lwekuiper\StatamicHubspot\Http\Controllers\FormConfigController;
use Lwekuiper\StatamicHubspot\Http\Controllers\GetContactPropertiesController;
use Lwekuiper\StatamicHubspot\Http\Controllers\GetFormFieldsController;

Route::name('hubspot.')->prefix('hubspot')->group(function () {
    Route::get('/', [FormConfigController::class, 'index'])->name('index');
    Route::get('/{form}/edit', [FormConfigController::class, 'edit'])->name('edit');
    Route::patch('/{form}', [FormConfigController::class, 'update'])->name('update');
    Route::delete('/{form}', [FormConfigController::class, 'destroy'])->name('destroy');

    Route::get('form-fields/{form}', [GetFormFieldsController::class, '__invoke'])->name('form-fields');
    Route::get('contact-properties', [GetContactPropertiesController::class, '__invoke'])->name('contact-properties');
});
