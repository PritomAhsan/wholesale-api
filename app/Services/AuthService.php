<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): User
    {
        $user = User::create([

            'first_name' => $data['first_name'],

            'last_name' => $data['last_name'] ?? null,

            'email' => $data['email'],

            'phone' => $data['phone'] ?? null,

            'password' => $data['password'],

            'status' => 'active',

        ]);

        $user->assignRole('Customer');

        return $user;
    }

    public function login(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Your account is not active.'],
            ]);
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);

        $token = $user->createToken('customer-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function me(User $user): User
    {
        return $user;
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Send a reset link, pointing back to whichever frontend the request
     * came from (admin panel or storefront use different reset pages).
     * Always succeeds from the caller's point of view — we don't reveal
     * whether the email exists.
     */
    public function forgotPassword(string $email, ?string $redirectUrl = null): ?string
    {
        $issuedToken = null;

        Password::sendResetLink(
            ['email' => $email],
            function (User $user, string $token) use ($redirectUrl, &$issuedToken) {

                $issuedToken = $token;

                $base = $redirectUrl
                    ? rtrim($redirectUrl, '/')
                    : rtrim(config('app.frontend_url', config('app.url')), '/');

                $url = $base . '?token=' . $token . '&email=' . urlencode($user->email);

                $user->notify(new ResetPasswordNotification($url));

            }
        );

        return $issuedToken;
    }

    public function resetPassword(array $data): void
    {
        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function (User $user, string $password) {

                $user->forceFill(['password' => $password])->save();

                $user->tokens()->delete();

            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }
}
