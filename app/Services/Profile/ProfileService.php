<?php

namespace App\Services\Profile;

use App\Repositories\Eloquent\ProfileRepository;
use App\Services\BaseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProfileService extends BaseService
{
    protected $profileRepo;
    public function __construct(ProfileRepository $profileRepo)
    {
        $this->profileRepo = $profileRepo;
    }

    public function updateProfile($user, array $data)
    {
        return $this->atomic(function () use ($user, $data) {
            if (isset($data['ktp_image'])) {

                $oldKtpPath = $user->profile?->ktp_path;

                if ($oldKtpPath && Storage::disk('local')->exists($oldKtpPath)) {
                    if (!filter_var($oldKtpPath, FILTER_VALIDATE_URL)) {
                        Storage::disk('local')->delete($oldKtpPath);
                    }
                }

                $file = $data['ktp_image'];
                $timestamp = date('His');
                $fileName = "ktp-{$timestamp}-{$user->id}.jpg";

                $image = Image::read($file);
                $image->scale(width: 800);
                $encoded = $image->toJpeg(70);

                $path = "ktp_images/{$fileName}";
                Storage::disk('local')->put($path, (string) $encoded);

                $data['ktp_path'] = $path;
            }

            $profile = $this->profileRepo->updateOrCreate(
                ['user_id' => $user->id],
                $data
            );

            $user->load('profile');

            return $user;
        });
    }
}
