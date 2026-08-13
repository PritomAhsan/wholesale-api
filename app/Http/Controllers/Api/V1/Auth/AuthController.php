<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\Auth\UserResource;
use App\Services\AuthService;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class AuthController extends ApiController
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register(
            $request->validated()
        );

        $token = $user
            ->createToken('customer-token')
            ->plainTextToken;

        return $this->success([

            'user' => new UserResource($user),

            'token' => $token,

        ], 'Registration successful', 201);
    }

    public function login(LoginRequest $request)
    {
        try {

            $result = $this->authService->login(
                $request->validated()
            );

            return $this->success([

                'user' => new UserResource($result['user']),

                'token' => $result['token'],

            ], 'Login successful');

        } catch (ValidationException $e) {

            return $this->error(
                'Authentication failed',
                $e->errors(),
                422
            );

        }
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $data = $request->validated();

        $token = $this->authService->forgotPassword(
            $data['email'],
            $data['redirect_url'] ?? null
        );

        $response = [];

        // Dev convenience: MAIL_MAILER=log means reset emails only land
        // in storage/logs, not an inbox — surface the token directly so
        // local testing doesn't require tailing the log file.
        if (app()->environment('local') && $token) {
            $response['debug_token'] = $token;
        }

        return $this->success(
            $response,
            'If that email exists, a password reset link has been sent.'
        );
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {

            $this->authService->resetPassword(
                $request->validated() + [
                    'password_confirmation' => $request->input('password_confirmation'),
                ]
            );

        } catch (ValidationException $e) {

            return $this->error(
                'Password reset failed',
                $e->errors(),
                422
            );

        }

        return $this->success(null, 'Password has been reset successfully.');
    }

    public function me(Request $request)
    {
        $user = $this->authService->me(
            $request->user()
        );

        return $this->success([
            'user' => new UserResource($user)
        ]);
    }

    public function logout(Request $request)
    {
        $this->authService->logout(
            $request->user()
        );

        return $this->success(
            null,
            'Logout successful'
        );
    }
}
