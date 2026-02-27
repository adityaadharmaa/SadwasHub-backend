<?php

namespace App\Services\Room;

use App\Repositories\Interfaces\RoomRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class RoomService extends BaseService
{
    protected $roomRepo;
    public function __construct(RoomRepositoryInterface $roomRepo)
    {
        $this->roomRepo = $roomRepo;
    }

    public function getAllRooms(?string $search = null, ?string $status = null, ?string $roomTypeId = null, int $perPage = 10)
    {
        return $this->roomRepo->getAll($search, $status, $roomTypeId, $perPage);
    }

    public function getRoomById($id)
    {
        $room = $this->roomRepo->find($id);
        if (!$room) {
            throw ValidationException::withMessages(['id' => 'Kamar tidak ditemukan.']);
        }
        return $room->load(['type', 'attachments']);
    }

    public function createRoom(array $data)
    {
        return $this->atomic(function () use ($data) {
            $images = $data['images'] ?? [];
            unset($data['images']);

            $room = $this->roomRepo->create($data);

            if (!empty($images) && is_array($images)) {
                $this->processAndSaveImages($room, $images);
            }
            return $room->load(['type', 'attachments']);
        });
    }

    public function updateRoom($id, array $data)
    {
        return $this->atomic(function () use ($id, $data) {
            $room = $this->roomRepo->find($id);
            if (!$room) {
                throw ValidationException::withMessages(['id' => 'Kamar tidak ditemukan.']);
            }

            $newImages = $data['images'] ?? [];
            $retainedImageIds = $data['retained_images'] ?? [];
            unset($data['images'], $data['retained_images']);

            if (!empty($data)) {
                $this->roomRepo->update($id, $data);
            }

            // Hapus gambar lama yang tidak ada di array retained_images
            $existingAttachments = $room->attachments()->get();
            foreach ($existingAttachments as $attachment) {
                if (!in_array($attachment->id, (array)$retainedImageIds)) {
                    if (Storage::disk('public')->exists($attachment->file_path)) {
                        Storage::disk('public')->delete($attachment->file_path);
                    }
                    $attachment->delete();
                }
            }

            // Upload gambar baru yang disisipkan
            if (!empty($newImages) && is_array($newImages)) {
                $this->processAndSaveImages($room, $newImages);
            }

            return $room->fresh()->load(['type', 'attachments']);
        });
    }

    public function deleteRoom($id)
    {
        return $this->atomic(function () use ($id) {
            $room = $this->roomRepo->find($id);
            if (!$room) {
                throw ValidationException::withMessages(['id' => 'Kamar tidak ditemukan.']);
            }

            // Validasi: Jangan biarkan admin menghapus kamar yang sedang diisi (occupied)
            if ($room->status === 'occupied') {
                throw ValidationException::withMessages(['status' => 'Tidak dapat menghapus kamar yang sedang disewa.']);
            }

            foreach ($room->attachments as $attachment) {
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                $attachment->delete();
            }

            return $this->roomRepo->delete($id);
        });
    }

    public function processAndSaveImages($room, array $images)
    {
        // Inisialisasi image manager dengan driver GD
        $manager = new ImageManager(new Driver());

        foreach ($images as $image) {
            // 1. Baca file yang diunggah
            $img = $manager->read($image);

            // 2. Buat format nama: Room-(Nomor Kamar)-(Tanggal & Waktu)-(Random String).webp
            $dateString = now()->format('Ymd_His'); // Contoh: 20260226_115020
            $randomString = Str::random(5); // Contoh: aBcD1 (Mencegah nama bentrok jika upload 5 foto sekaligus)

            // Menggabungkan semuanya menjadi nama file yang cantik
            $filename = "Room-{$room->room_number}-{$dateString}-{$randomString}.webp";

            $path = 'rooms/' . $filename;

            // 3. Konversi ke WebP dengan kualitas 80%
            $encodedImage = $img->toWebp(80);

            // 4. Simpan ke Storage Laravel (public/rooms)
            Storage::disk('public')->put($path, (string) $encodedImage);

            // 5. Simpan jejaknya di database (tabel attachments)
            $room->attachments()->create([
                'file_path' => $path,
                'file_type' => 'webp', // Kita selalu set ke webp karena sudah dikonversi
            ]);
        }
    }
}
