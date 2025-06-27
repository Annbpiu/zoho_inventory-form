<?php

use App\Http\Controllers\ZohoAuthController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/zoho/oauth/callback', [ZohoAuthController::class, 'callback'])->name('zoho.callback');
