<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\VerifyProfileRequest;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use App\Models\UserProfile;
use App\Services\Profile\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('profile');
        return $this->successResponse(new UserResource($user), 'Data profile berhasil diambil');
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();

        $update = $this->profileService->updateProfile($user, $request->validated());

        return $this->successResponse(
            new UserResource($update),
            'Data profile berhasil diupdate'
        );
    }

    public function verify(VerifyProfileRequest $request, $profileId)
    {
        $updatedProfile = $this->profileService->verifyProfile($profileId, $request->validated());

        $status = $request->is_verified ? 'disetujui' : 'ditolak';
        return $this->successResponse(
            new UserProfileResource($updatedProfile),
            "Profile berhasil diverifikasi dan $status."
        );
    }

    public function showKtp(Request $request, $filename)
    {
        $profileOwner = UserProfile::where('ktp_path', 'like', "%{$filename}%")->firstOrFail();

        Gate::authorize('viewKtp', $profileOwner);

        $path = "ktp_images/" . $filename;

        if (!Storage::disk('local')->exists($path)) {
            return $this->errorResponse('File tidak ditemukan', 404);
        }

        $viewerName = strtoupper($request->user()->profile->full_name ?? $request->user()->email);
        $accessTime = now()->format('d M Y H:i:s');

        $watermarkText = "CONFIDENTIAL SADEWAS HUB\nDIAKSES OLEH:\n{$viewerName}\nPADA:\n{$accessTime}";

        $realPath = Storage::disk('local')->path($path);
        $image = Image::read($realPath);

        $fontPath = public_path('fonts/Roboto-Bold.ttf');

        $image->text($watermarkText, $image->width() / 2, $image->height() / 2, function ($font) use ($fontPath, $image) {
            if (file_exists($fontPath)) {
                $font->file($fontPath);
            }

            $fontSize = $image->width() * 0.025;
            $font->size($fontSize > 15 ? $fontSize : 15);

            $font->color('rgba(255,255, 255,0.5)');

            $font->align('center');
            $font->valign('middle');
            $font->lineHeight(1.5);
            $font->angle(45);
            $image->scale(width: 600);
        });

        return response($image->toJpeg(80))
            ->header('Content-Type', 'image/jpeg')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
