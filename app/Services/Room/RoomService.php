<?php

namespace App\Services\Room;

use App\Repositories\Interfaces\RoomRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Validation\ValidationException;

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
        return $room->load('type');
    }

    public function createRoom(array $data)
    {
        return $this->atomic(function () use ($data) {
            $room = $this->roomRepo->create($data);
            return $room->load('type');
        });
    }

    public function updateRoom($id, array $data)
    {
        return $this->atomic(function () use ($id, $data) {
            $room = $this->roomRepo->find($id);
            if (!$room) {
                throw ValidationException::withMessages(['id' => 'Kamar tidak ditemukan.']);
            }

            if (!empty($data)) {
                $this->roomRepo->update($id, $data);
            }

            return $room->fresh()->load('type');
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

            return $this->roomRepo->delete($id);
        });
    }
}
