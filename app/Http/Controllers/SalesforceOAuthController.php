<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Enums\Constant;
use App\Models\Integration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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

        $token = Token::updateOrCreate(
            [
                'user_id' => auth()->user()->id,
                'app_id' => session('salesforce_app_id'),
            ],
            [
                'token' => Crypt::encryptString($user->token),
            ]
        );

        if (session('type') == Constant::FIRST_APP) {
            Integration::query()->find(session('integration_id'))->update([
                'first_app_token_id' => $token->id,
            ]);
        }

        if (session('type') == Constant::SECOND_APP) {
            Integration::query()->find(session('integration_id'))->update([
                'second_app_token_id' => $token->id,
            ]);
        }

        $integration_id = session('integration_id');

        session()->forget([
            'salesforce_app_id',
            'integration_id',
            'type'
        ]);

        return redirect()->route('filament.client.resources.integrations.setup', $integration_id);
    }
}
