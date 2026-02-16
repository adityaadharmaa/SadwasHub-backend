<?php

namespace App\Services\Auth;

use App\Repositories\Interfaces\ProfileRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Socialite;

class AuthService extends BaseService
{
    protected $userRepo;
    protected $profileRepo;
    public function __construct(
        UserRepositoryInterface $userRepo,
        ProfileRepositoryInterface $profileRepo

    ) {
        $this->userRepo = $userRepo;
        $this->profileRepo = $profileRepo;
    }

    /**
     * Register manual (Email & Password)
     */

    public function register(array $data)
    {
        return $this->atomic(function () use ($data) {
            $user = $this->userRepo->create([
                'email' => $data['email'],
                'password' => Hash::make($data['password'])
            ]);

            $user->assignRole('tenant');

            $this->profileRepo->create([
                'user_id' => $user->id,
                'full_name' => $data['full_name'] ?? $data['name'] ?? 'Pengguna Baru',
                'nik' => 'TEMP-' . Str::random(10),
                'phone_number' => '-',
                'ktp_path' => '-',
                'is_verified' => false
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token
            ];
        });
    }

    /**
     * Login manual (Email & Password)
     */

    public function login(array $data)
    {
        $user = $this->userRepo->findByEmail($data['email']);

        if (! $user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    /**
     * Social login (Google)
     * Frontend mengirimkan 'code' dari Google, backend menukarnya dengan user data.
     */
    public function handleSocialLogin(string $provider)
    {
        return $this->atomic(function () use ($provider) {
            try {
                $socialUser = Socialite::driver($provider)->stateless()->user();
            } catch (\Exception $e) {
                throw ValidationException::withMessages(['login' => 'Gagal login dengan ' . $provider . '. Token tidak valid.']);
            }

            $user = $this->userRepo->findByEmail($socialUser->getEmail());

            if (!$user) {
                // Register via google
                $user = $this->userRepo->create([
                    'email' => $socialUser->getEmail(),
                    'password' => null,
                    'social_id' => $socialUser->getId(),
                    'social_type' => $provider,
                    'email_verified_at' => now()
                ]);

                $user->assignRole('tenant');

                $this->profileRepo->create([
                    'user_id' => $user->id,
                    'full_name' => $socialUser->getName(),
                    'nik' => 'TEMP-' . Str::random(10),
                    'phone_number' => '-',
                    'ktp_path' => $socialUser->getAvatar(),
                    'is_verified' => false
                ]);
            } else {
                // Jika user sudah ada maka link account
                if (!$user->social_id) {
                    $this->userRepo->update($user->id, [
                        'social_id' => $socialUser->getId(),
                        'social_type' => $provider
                    ]);
                }
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token
            ];
        });
    }
}
