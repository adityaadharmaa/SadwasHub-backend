<?php

namespace App\Services\Room;

use App\Repositories\Interfaces\FacilityRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Validation\ValidationException;

class FacilityService extends BaseService
{
    protected $facilityRepo;
    public function __construct(FacilityRepositoryInterface $facilityRepo)
    {
        $this->facilityRepo = $facilityRepo;
    }

    public function getAllFacilities(?string $search = null, int $perPage = 10)
    {
        return $this->facilityRepo->getAll($search, $perPage);
    }

    public function getFacilityById($id)
    {
        $facility = $this->facilityRepo->find($id);
        if (!$facility) {
            throw ValidationException::withMessages(['id' => 'Fasilitas tidak ditemukan.']);
        }
        return $facility;
    }

    public function createFacility(array $data)
    {
        return $this->atomic(function () use ($data) {
            return $this->facilityRepo->create($data);
        });
    }

    public function updateFacility($id, array $data)
    {
        return $this->atomic(function () use ($id, $data) {
            $facility = $this->facilityRepo->find($id);
            if (!$facility) {
                throw ValidationException::withMessages(['id' => 'Fasilitas tidak ditemukan.']);
            }

            if (!empty($data)) {
                $this->facilityRepo->update($id, $data);
            }

            return $this->facilityRepo->find($id);
        });
    }

    public function deleteFacility($id)
    {
        return $this->atomic(function () use ($id) {
            $facility = $this->facilityRepo->find($id);
            if (!$facility) {
                throw ValidationException::withMessages(['id' => 'Fasilitas tidak ditemukan.']);
            }

            // Jika diperlukan, cek apakah fasilitas ini sedang dipakai di Room Type sebelum dihapus
            // if ($facility->roomTypes()->count() > 0) { ... throw error ... }

            return $this->facilityRepo->delete($id);
        });
    }
}
