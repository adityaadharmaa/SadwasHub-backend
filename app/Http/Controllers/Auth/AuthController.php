<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Laravel\Socialite\Socialite;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());

        return $this->successResponse([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 'Registrasi Berhasil.', 201);
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());

        return $this->successResponse([
            'user' => new UserResource($result['user']),
            'token' => $result['token']
        ], 'Login Berhasil');
    }

    public function redirectToProvider($provider)
    {
        if ($provider !== 'google') {
            return $this->errorResponse('Provider tidak didukung.', 400);
        }

        $url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();

        return $this->successResponse(['url' => $url], 'Silakan redirect ke URL berikut.');
    }

    public function handleProviderCallback($provider)
    {
        try {
            $result = $this->authService->handleSocialLogin($provider);

            return $this->successResponse([
                'user' => new UserResource($result['user']),
                'token' => $result['token']
            ], 'Login social berhasil.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
