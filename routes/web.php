<?php

use App\Http\Controllers\MailchimpOAuthController;
use App\Http\Controllers\SalesforceOAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('auth/salesforce', [SalesforceOAuthController::class, 'redirectToSalesforce'])->name('auth.salesforce');
    Route::get('auth/salesforce/callback', [SalesforceOAuthController::class, 'handleSalesforceCallback'])->name('auth.salesforce.callback');

    Route::get('auth/mailchimp', [MailchimpOAuthController::class, 'redirectToMailchimp'])->name('auth.mailchimp');
    Route::get('auth/mailchimp/callback', [MailchimpOAuthController::class, 'handleMailchimpCallback'])->name('auth.mailchimp.callback');
});
