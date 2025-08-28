<?php

use App\Http\Controllers\PushController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

 

Route::post('/save-token', [PushController::class, 'saveToken'])->name('save-token');
Route::post('/send-notification', [PushController::class, 'sendNotification'])->name('send.notification');
 

