<?php

namespace App\Services\Auth;

use App\Repositories\Interfaces\ProfileRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
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
                'password' => Hash::make($data['password']),
                'email_verified_at' => null
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

            event(new Registered($user));

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

        $expiration = isset($data['remember_me']) && $data['remember_me']
            ? now()->addDays(30)
            : now()->addDay();

        $token = $user->createToken('auth_token', ['*'], $expiration)->plainTextToken;

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

    /**
     * Logic Mengirim Ulang link verifikasi email
     */
    public function resendEmailVerification($user)
    {
        if ($user->hasVerifiedEmail()) {
            return [
                'status' => 'already_verified',
                'message' => 'Email sudah terverifikasi.'
            ];
        }

        $user->sendEmailVerificationNotification();

        return [
            'status' => 'verification_sent',
            'message' => 'Link verifikasi baru telah dikirim ke email Anda.'
        ];
    }


    /**
     * Logic Lupa Password
     */
    public function forgotPassword(array $data)
    {
        $status = Password::broker()->sendResetLink($data);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)]
            ]);
        }

        return $status;
    }

    /**
     * Logic Reset Password
     */
    public function resetPassword(array $data)
    {
        $status = Password::broker()->reset(
            $data,
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)]
            ]);
        }

        return $status;
    }
}
