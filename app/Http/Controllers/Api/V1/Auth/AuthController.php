<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Auth\RegisterRequest;
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
