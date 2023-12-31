<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Enums\AppType;
use App\Models\Integration;
use App\Settings\MailchimpSettings;
use Illuminate\Support\Facades\Crypt;
use Laravel\Socialite\Facades\Socialite;

class MailchimpOAuthController extends Controller
{
    public function redirectToMailchimp()
    {
        if (session()->has(['mailchimp_app_id', 'mailchimp_integration_id', 'mailchimp_type'])) {
            return Socialite::driver('mailchimp')->redirect();
        }

        abort(404);
    }

    public function handleMailchimpCallback()
    {
        if (session()->has(['mailchimp_app_id', 'mailchimp_integration_id', 'mailchimp_type'])) {
            $user = Socialite::driver('mailchimp')->user();

            $token = Token::query()->create([
                'user_id' => auth()->user()->id,
                'app_id' => session('mailchimp_app_id'),
                'token' => Crypt::encryptString($user->token)
            ]);

            if (session('mailchimp_type') == getEnumValue(AppType::FIRST_APP) || session('mailchimp_type') == getEnumValue(AppType::SECOND_APP)) {
                $updateDataKey = session('mailchimp_type') == getEnumValue(AppType::FIRST_APP) ? 'first_app' : 'second_app';

                Integration::query()->find(session('mailchimp_integration_id'))->update([
                    "{$updateDataKey}_token_id" => $token->id,
                    "{$updateDataKey}_settings" => MailchimpSettings::make()->region($user->user['dc'])->getSettings(),
                ]);
            }

            $integration_id = session('mailchimp_integration_id');

            session()->forget([
                'mailchimp_app_id',
                'mailchimp_integration_id',
                'mailchimp_type'
            ]);

            return redirect()->route('filament.client.resources.integrations.setup', $integration_id);
        }

        abort(404);
    }
}
