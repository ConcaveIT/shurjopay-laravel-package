<?php

use Illuminate\Support\Facades\Route;
use smukhidev\ShurjopayLaravelPackage\ShurjopayController;

Route::post('/response', [ShurjopayController::class, 'response'])->name('shurjopay.response');
