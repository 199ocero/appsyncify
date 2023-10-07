<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Enums\Constant;
use App\Models\Integration;
use Illuminate\Http\Request;
use App\Settings\SalesforceSettings;
use Illuminate\Support\Facades\Crypt;
use Laravel\Socialite\Facades\Socialite;

class SalesforceOAuthController extends Controller
{
    public function redirectToSalesforce()
    {
        if (session()->has(['salesforce_app_id', 'integration_id', 'type'])) {
            return Socialite::driver('salesforce')->redirect();
        }

        abort(404);
    }

    public function handleSalesforceCallback()
    {
        if (session()->has(['salesforce_app_id', 'integration_id', 'type'])) {
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

            if (session('type') == Constant::FIRST_APP || session('type') == Constant::SECOND_APP) {
                $updateDataKey = session('type') == Constant::FIRST_APP ? 'first_app' : 'second_app';

                Integration::query()->find(session('integration_id'))->update([
                    "{$updateDataKey}_token_id" => $token->id,
                    "{$updateDataKey}_settings" => SalesforceSettings::make()->domain($user->accessTokenResponseBody['instance_url'])->getSettings(),
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

        abort(404);
    }
}
