<?php

namespace App\Services\Room;

use App\Http\Resources\RoomTypeResource;
use App\Repositories\Interfaces\RoomTypeRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Validation\ValidationException;

class RoomTypeService extends BaseService
{
    protected $roomTypeRepo;
    public function __construct(RoomTypeRepositoryInterface $roomTypeRepo)
    {
        $this->roomTypeRepo = $roomTypeRepo;
    }

    public function getAllRoomTypes(?string $search = null, int $perPage = 10)
    {
        return $this->roomTypeRepo->getAll($search, $perPage);
    }

    public function getRoomTypeById($id)
    {
        $roomType = $this->roomTypeRepo->find($id);
        if (!$roomType) {
            throw ValidationException::withMessages(['id' => 'Tipe kamar tidak ditemukan.']);
        }

        return $roomType->load('facilities');
    }

    public function createRoomType(array $data)
    {
        return $this->atomic(function () use ($data) {
            $facilities = $data['facilities'] ?? null;

            unset($data['facilities']);
            $roomType = $this->roomTypeRepo->create(
                $data
            );

            if ($facilities !== null) {
                $roomType->facilities()->sync($facilities);
            }

            return $roomType->load('facilities');
        });
    }

    public function UpdateRoomType($id, array $data)
    {
        return $this->atomic(function () use ($id, $data) {
            $roomType = $this->roomTypeRepo->find($id);
            if (!$roomType) {
                throw ValidationException::withMessages(['id' => 'Tipe kamar tidak ditemukan.']);
            }

            // 1. Ambil data facilities (jika ada) lalu hapus dari array $data utama
            $facilities = $data['facilities'] ?? null;
            unset($data['facilities']);

            // 2. Update data utama (Hanya akan mengupdate kolom yang dikirimkan saja)
            if (!empty($data)) {
                $this->roomTypeRepo->update($id, $data);
            }

            // 3. Update relasi fasilitas (jika dikirimkan di request)
            if ($facilities !== null) {
                $roomType->facilities()->sync($facilities);
            }
            return $roomType->fresh()->load('facilities');
        });
    }

    public function deleteRoomType($id)
    {
        return $this->atomic(function () use ($id) {
            $roomType = $this->roomTypeRepo->find($id);
            if (!$roomType) {
                throw ValidationException::withMessages(['id' => 'Tipe kamar tidak ditemukan.']);
            }

            // Opsional: Validasi apakah tipe kamar ini sedang dipakai di tabel 'rooms'
            // Jika Anda sudah punya relasinya di model RoomType:
            // if ($roomType->rooms()->count() > 0) {
            //     throw ValidationException::withMessages(['error' => 'Tidak bisa dihapus karena masih ada unit kamar yang menggunakan tipe ini.']);
            // }

            // Detach semua fasilitas sebelum tipe kamar dihapus (karena pakai soft deletes)
            $roomType->facilities()->detach();

            return $this->roomTypeRepo->delete($id);
        });
    }
}
