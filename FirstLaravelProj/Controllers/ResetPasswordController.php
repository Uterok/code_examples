<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\PasswordReset;
use App\Models\Users\User;
use App\Notifications\Auth\PasswordResetRequest;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Create token password reset
     *
     * @param  [string] email
     *
     * @return [string] message
     */
    public function createFromApi(): JsonResponse
    {
        request()->validate([
            'email' => 'required|string|email',
        ]);
        $user = User::where('email', request()->email)->first();
        if (! $user) {
            return response()->json([
                'message' => 'We can`t find a user with that e-mail address.',
            ], 404);
        }
        $password_reset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => PasswordReset::generateToken(),
            ]
        );
        if ($user && $password_reset) {
            $user->notify(
                new PasswordResetRequest($password_reset->token)
            );
        }
        return response()->json([
            'success' => 1,
        ]);
    }
    /**
     * Find token password reset
     *
     * @param  [string] $token
     *
     * @return [string] message
     * @return [json] password_reset object
     */
    public function findFromApi($token): JsonResponse
    {
        $password_reset = PasswordReset::where('token', $token)
            ->first();
        if (! $password_reset) {
            return response()->json([
                'message' => 'This password reset token is invalid.',
            ], 404);
        }
        if (Carbon::parse($password_reset->updated_at)->addMinutes(PasswordReset::TOKEN_EXPIRED_IN)->isPast()) {
            $password_reset->delete();
            return response()->json([
                'message' => 'This password reset token is invalid.',
            ], 404);
        }
        return response()->json($password_reset);
    }
    /**
     * Reset password
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @param  [string] token
     *
     * @return [string] message
     * @return [json] user object
     */
    public function resetFromApi(): JsonResponse
    {
        request()->validate([
            'password' => 'required|string|confirmed',
            'token' => 'required|string',
        ]);
        $password_reset = PasswordReset::where([
            ['token', request()->token],
        ])->first();
        if (! $password_reset) {
            return response()->json([
                'message' => 'This password reset token is invalid.',
            ], 404);
        }
        $user = User::where('email', $password_reset->email)->first();
        if (! $user) {
            return response()->json([
                'message' => 'We can`t find a user with that e-mail address.',
            ], 404);
        }
        $user->password = bcrypt(request()->password);
        $user->save();
        $password_reset->delete();
        return response()->json($user);
    }
}
