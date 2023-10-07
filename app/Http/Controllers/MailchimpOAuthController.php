<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Enums\Constant;
use App\Models\Integration;
use Illuminate\Http\Request;
use App\Settings\SalesforceSettings;
use Illuminate\Support\Facades\Crypt;
use Laravel\Socialite\Facades\Socialite;

class MailchimpOAuthController extends Controller
{
    public function redirectToMailchimp()
    {
        return Socialite::driver('mailchimp')->redirect();
    }

    public function handleMailchimpCallback()
    {
        $user = Socialite::driver('mailchimp')->user();
        dd($user);
    }
}
