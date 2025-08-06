<?php

use Illuminate\Support\Facades\Route;
use dits\ShurjopayLaravelPackage\ShurjopayController;

Route::post('/response', [ShurjopayController::class, 'response'])->name('shurjopay.response');
