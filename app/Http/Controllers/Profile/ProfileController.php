<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\VerifyProfileRequest;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use App\Models\UserProfile;
use App\Models\Attachment;
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
        $user = $request->user()->load(['profile.attachments']);

        $permissions = $user->getAllPermissions()->pluck('name');
        $roles = $user->getRoleNames();

        $data = [
            'user' => new UserResource($user),
            'roles' => $roles,
            'permissions' => $permissions,
        ];

        return $this->successResponse($data, 'Data profile berhasil diambil');
    }


    public function update(UpdateProfileRequest $request)
    {
        // 1. CEK PERMISSION SEBELUM MENGEKSEKUSI LOGIKA
        if (!$request->user()->can('edit-profile')) {
            return $this->errorResponse('Akses Ditolak: Anda tidak memiliki izin untuk mengubah profil.', 403);
        }

        $user = $request->user();

        $update = $this->profileService->updateProfile($user, $request->validated());

        return $this->successResponse(
            new UserResource($update),
            'Data profile berhasil diupdate'
        );
    }

    public function verify(VerifyProfileRequest $request, $profileId)
    {
        // 1. CEK PERMISSION ADMIN SEBELUM MEMVERIFIKASI
        if (!$request->user()->can('verify-ktp')) {
            return $this->errorResponse('Akses Ditolak: Anda tidak memiliki wewenang untuk memverifikasi dokumen KTP.', 403);
        }

        $updatedProfile = $this->profileService->verifyProfile($profileId, $request->validated());

        $status = $request->is_verified ? 'disetujui' : 'ditolak';
        return $this->successResponse(
            new UserProfileResource($updatedProfile),
            "Profile berhasil diverifikasi dan $status."
        );
    }

    public function showKtp(Request $request, $filename)
    {
       $path = "ktp_images/" . $filename;

        // 1. Cari data gambar di tabel attachments (Sistem Baru)
        $attachment = Attachment::where('file_path', $path)
            ->where('file_type', 'ktp')
            ->first();

        // 2. Tentukan Pemilik Profil dengan Sistem Fallback
        if ($attachment) {
            $profileOwner = $attachment->attachable;
        } else {
            // Jika tidak ada di attachments, cari di tabel user_profiles (Sistem Lama)
            $profileOwner = UserProfile::where('ktp_path', $path)->first();
            
            // Jika di kedua tabel tidak ada sama sekali
            if (!$profileOwner) {
                return $this->errorResponse('Data KTP tidak ditemukan di database.', 404);
            }
        }

        // 3. Validasi Hak Akses (Hanya pemilik atau Admin)
        Gate::authorize('viewKtp', $profileOwner);

        // 4. Pastikan file fisiknya ada di private storage
        if (!Storage::disk('local')->exists($path)) {
            return $this->errorResponse('File fisik tidak ditemukan di server.', 404);
        }

        // 5. Persiapkan Watermark
        $viewerName = strtoupper($request->user()->profile->full_name ?? $request->user()->email);
        $accessTime = now()->format('d M Y H:i:s');

        $watermarkText = "CONFIDENTIAL SADEWAS HUB\nDIAKSES OLEH:\n{$viewerName}\nPADA:\n{$accessTime}";

        // 6. Baca dan Modifikasi Gambar
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
        });

        $image->scale(width: 600);

        return response($image->toJpeg(80))
            ->header('Content-Type', 'image/jpeg')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
