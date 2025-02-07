<?php

use App\Http\Controllers\ClientOuter\Google\GmailController;
use Illuminate\Support\Facades\Route;



// Route 


Route::get('/skap', [GmailController::class, 'fetchEmails']);

