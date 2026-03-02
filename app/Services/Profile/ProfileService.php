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
            $ktpFile = $data['ktp_image'] ?? null;
            unset($data['ktp_image']);

            $profile = $this->profileRepo->updateOrCreate(
                ['user_id' => $user->id],
                $data
            );

            if($ktpFile){
                $oldKtp = $profile->attachments()->where('file_type', 'ktp')->first();

                if($oldKtp){
                    if(Storage::disk('local')->exists($oldKtp->file_path)){
                        Storage::disk('local')->delete($oldKtp->file_path);
                    }
                    $oldKtp->delete();
                }

                $timestamp = date('His');
                $fileName = "ktp-{$timestamp}-{$user->id}.jpg";
                $path = "ktp_images/{$fileName}";

                $image = Image::read($ktpFile);
                $image->scale(width: 800);
                $encoded = $image->toJpeg(70);

                Storage::disk('local')->put($path, (string) $encoded);

                $profile->attachments()->create([
                    'file_path' => $path,
                    'file_type' => 'ktp'
                ]);
            }

            $user->load(['profile.attachments']);

            return $user;
        });
    }

    public function verifyProfile($profileId, array $data)
    {
        return $this->atomic(function () use ($profileId, $data) {
            $this->profileRepo->update($profileId, [
                'is_verified' => $data['is_verified'],
                'admin_note' => $data['admin_note'] ?? null,
            ]);

            return $this->profileRepo->find($profileId);
        });
    }
}
