<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SalesforceOAuthController extends Controller
{
    public function redirectToSalesforce()
    {
        return Socialite::driver('salesforce')->redirect();
    }

    public function handleSalesforceCallback()
    {
        $user = Socialite::driver('salesforce')->user();
        dd($user);
        // $user now contains user information obtained from Salesforce.

        // Add your logic to handle the authenticated user here.
    }
}
