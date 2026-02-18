<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Auth\Events\Verified;
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

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $this->authService->forgotPassword($request->only('email'));

        return $this->successResponse(null, 'Link reset password telah dikirim ke email Anda.');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $this->authService->resetPassword($request->only('email', 'password', 'password_confirmation', 'token'));

        return $this->successResponse(null, 'Password berhasil diubah. Silakan login kembali.');
    }

    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();

        $result = $this->authService->resendEmailVerification($user);

        if ($result['status'] === 'already_verified') {
            return $this->errorResponse($result['message'], 400);
        }

        return $this->successResponse(null, $result['message']);
    }

    public function verifyEmail(Request $request)
    {
        if (!$request->hasValidSignature()) {
            return $this->errorResponse('Link verifikasi tidak valid atau sudah kedaluwarsa.', 401);
        }

        $user = User::findOrFail($request->route('id'));

        // 2. Cek apakah sudah diverifikasi sebelumnya
        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(null, 'Email sudah terverifikasi sebelumnya.');
        }

        // 3. Tandai sebagai terverifikasi
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->successResponse(null, 'Email berhasil diverifikasi! Anda sekarang dapat menikmati fitur penuh Sadewas Hub.');
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
