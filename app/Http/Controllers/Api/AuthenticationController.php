<?php

namespace App\Http\Controllers\Api;

use App\Events\LogoutOtherBrowser;
use App\Http\Controllers\Api\Concern\AllowedDomain;
use App\Http\Controllers\Api\Concern\CanLogoutOtherDevices;
use App\Http\Controllers\Api\Concern\CanValidateProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginViaEmailRequest;
use App\Http\Requests\LogOutOtherBrowserRequest;
use App\Http\Resources\ProfileResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Throwable;

class AuthenticationController extends Controller
{
    use AllowedDomain;
    use CanLogoutOtherDevices;
    use CanValidateProvider;

    public string $url;

    /**
     * Login via Provider
     *
     * @param  string  $provider  Possible options: zoho
     * @return JsonResponse
     *
     * @unauthenticated
     */
    public function loginViaProvider(string $provider)
    {
        $this->validateProvider($provider);

        if ($provider === 'zoho') {
            $this->url = Socialite::driver($provider)
                ->setScopes(['AaaServer.profile.Read'])
                ->with([
                    'prompt' => 'consent',
                    'access_type' => 'offline',
                    'provider' => $provider,
                ])
                ->stateless()->redirect()->getTargetUrl();
        }

        return response()->json([
            'success' => (bool) $this->url,
            'link' => $this->url ?? '',
        ]);

    }

    /**
     * Login Provider Callback
     *
     * @param  $provider
     * @param  Request  $request
     * @return JsonResponse|void
     *
     * @throws Throwable
     */
    public function providerCallback($provider, Request $request)
    {
        $this->validateProvider($provider);

        if (is_null($request->code)) {
            throw new UnprocessableEntityHttpException('Code is required');
        }

        try {
            if ($provider === 'zoho') {
                // Get Access token from the code generated
                $tokenRequest = Socialite::driver($provider)->stateless()->getAccessTokenResponse($request->code);
                if (Arr::has($tokenRequest, 'error')) {
                    return response()->json([
                        'success' => false,
                        'message' => $tokenRequest['error'],
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                $accessToken = Arr::get($tokenRequest, 'access_token');

                $userProvider = Socialite::driver($provider)->stateless()->userFromToken($accessToken);

                if (! $this->isWhitelistedDomain($userProvider->email)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized email domain, please try again later.',
                    ], Response::HTTP_FORBIDDEN);
                }
                $userCreated = User::firstOrCreate(
                    [
                        'email' => $userProvider->email,
                    ],
                    [
                        'email_verified_at' => Carbon::now(),
                        'name' => $userProvider->name,
                        'avatar' => $userProvider->avatar,
                    ]
                );
                $userCreated->providers()->updateOrCreate(
                    [
                        'provider' => $provider,
                        'provider_id' => $userProvider->id,
                    ],
                    [
                        'avatar' => $userProvider->avatar,
                    ]
                );

                $token = $userCreated->createToken($request->device_name ?? 'nanshiki')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'token' => [
                        //@var string Token for authentication.
                        //@example 31|b2da4411aa4e6d153d6725a17c672b8177c071e60a05158ff19af75a3b5829aa
                        'accessToken' => $token,
                    ],
                    'user' => new ProfileResource($userCreated),
                ], Response::HTTP_OK);

            }
        } catch (Throwable $throwable) {
            throw new $throwable;
        }

    }

    /**
     * Login using Credentials
     *
     * @return JsonResponse
     *
     * @unauthenticated
     */
    public function loginViaEmail(LoginViaEmailRequest $request)
    {
        $user = User::where('email', $request->email)->whereNotNull('password')->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($request->header('User-Agent') ?? 'unknown')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => [
                'accessToken' => $token,
            ],
            'user' => new ProfileResource($user),
        ]);

    }

    /**
     * Logout Session
     *
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json('', Response::HTTP_ACCEPTED);
    }

    /**
     *  Logout Other Browser Session
     */
    public function logoutOtherDevices(LogOutOtherBrowserRequest $request)
    {

        $this->logoutOtherDevices($request);

        LogoutOtherBrowser::dispatch($request->user());
    }
}