<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Users\SocialAccount;
use App\Models\Users\User;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\User as SocialUser;
use Socialite;

class SocialAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware(['social', 'web']);
    }

    public function redirect(string $provider): RedirectResponse
    {
        $registration_flow = request()->query('registration_flow');

        return Socialite::driver($provider)->stateless()
                        ->with(['state' => json_encode(['registration_flow' => $registration_flow])])
                        ->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        try {
            $user_social = Socialite::driver($provider)->stateless()->user();
        } catch (\Exception $e) {
            return redirect(
                config('client.base_url') .
                '?error=Unable to login using ' . $provider . '. Please try again.'
            );
        }

        // get state from google request
        $state = request()->state;
        $decoded_state = json_decode($state, true);
        $registration_flow = $decoded_state['registration_flow'] ?? null;

        $user_info = $this->checkUserAccounts($user_social, $provider, $registration_flow);
        $user = $user_info['user'];

        Auth::login($user);

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return redirect(
            config('client.base_url') . '/' . config('client.auth_success_callback') .
            '?token=' . $accessToken
        );
    }

    public function checkUserAccounts(
        SocialUser $user_social, 
        string $provider, 
        ?string $registration_flow = null
    ): array
    {
        $user_social_info = $this->getUserSocialInfo($user_social, $provider);

        $user = User::findForPassportSocialite(
            $provider,
            $user_social_info['provider_user_id'],
            $user_social_info['email']
        );

        if (! ($user instanceof User)) {
            $user = User::create([
                'firstname' => $user_social_info['firstname'],
                'lastname' => $user_social_info['lastname'],
                'email' => $user_social_info['email'],
                'password' => bcrypt(Str::random(16)),
            ]);

            $user->assignRegistrationFlow($registration_flow, true);
        }

        $user_social = $user->social()->providerAccount($provider, $user_social_info['provider_user_id'])->first();
        if (! isset($user_social)) {
            SocialAccount::create([
                'provider' => $provider,
                'provider_user_id' => $user_social_info['provider_user_id'],
                'user_id' => $user->id,
            ]);
        }

        return ['user' => $user];
    }

    public function getUserSocialInfo(SocialUser $user_social, string $provider): ?array
    {
        //user socil info depends on provider
        switch ($provider) {
            case SocialAccount::SERVICE_GOOGLE:
                $user = $user_social->user;
                return $this->createUserInfo(
                    $user_social->getId(),
                    $user_social->getEmail(),
                    $user['given_name'],
                    $user['family_name']
                );
                break;
            default:
                return null;
                break;
        }
    }

    public function createUserInfo(string $provider_user_id, string $email, string $firstname, string $lastname): array
    {
        return [
            'provider_user_id' => $provider_user_id,
            'email' => $email,
            'firstname' => $firstname,
            'lastname' => $lastname,
        ];
    }
}
