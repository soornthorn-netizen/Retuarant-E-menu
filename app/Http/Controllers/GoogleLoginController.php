<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Auth\SocialLoginRequest;
use App\Models\LinkedGoogleAccount;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User as ProviderUser;

class GoogleLoginController extends Controller
{
    public function login(SocialLoginRequest $request)
    {
        try {
            $accessToken = $request->get('access_token');
            $provider = $request->get('provider');

            // Use stateless() for APIs to prevent session errors
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver($provider);
            $providerUser = $driver->stateless()->userFromToken($accessToken);

        } catch (Exception $exception) {
            // Return standard 401 Unauthorized JSON instead of a 200 OK
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token: ' . $exception->getMessage(),
            ], 401);
        }

        if ($providerUser) {
            $user = $this->findOrCreate($providerUser, $provider);

            // Create the Sanctum token directly (Skip auth()->login() completely)
            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Logged in successfully',
                'data' => [
                    'token' => $token,
                    'user'  => $user // Good practice to return the user info to the frontend
                ],
            ], 200);
        }

        // Fallback error if providerUser is empty
        return response()->json([
            'success' => false,
            'message' => 'Failed to Login. Please try again.',
        ], 400);
    }

    protected function findOrCreate(ProviderUser $providerUser, string $provider): User
    {
        // 1. Check if the social account is already linked
        $linkedGoogleAccount = LinkedGoogleAccount::query()
            ->where('provider_name', $provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        if ($linkedGoogleAccount) {
            return $linkedGoogleAccount->user;
        }

        $user = null;

        // 2. Check if a user with this email already exists
        if ($email = $providerUser->getEmail()) {
            $user = User::query()->where('email', $email)->first();
        }

        // 3. If user doesn't exist, create them
        if (!$user) {
            $email = $providerUser->getEmail();
            $name = $providerUser->getName()
                ?: ($providerUser->getNickname() ?: ($email ? Str::before($email, '@') : 'User'));

            $user = User::query()->create([
                'name'     => $name,
                'email'    => $email,
                'password' => Hash::make(Str::random(24)),
                'role'     => 'customer',
                'avatar'   => $providerUser->getAvatar(),
            ]);

            $user->markEmailAsVerified();
        }

        // 4. Link the social account to the user
        $user->linkedSocialAccounts()->create([
            'provider_id'   => $providerUser->getId(),
            'provider_name' => $provider,
        ]);

        return $user;
    }
}

