<?php

namespace App\Http\Controllers\Room;

use App\Http\Controllers\Controller;
use App\Http\Requests\Room\StoreFacilityRequest;
use App\Http\Requests\Room\UpdateFacilityRequest;
use App\Http\Resources\FacilityResource;
use App\Services\Room\FacilityService;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    protected $facilityService;

    public function __construct(FacilityService $facilityService)
    {
        $this->facilityService = $facilityService;
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);

        $facilities = $this->facilityService->getAllFacilities($search, $perPage);

        return FacilityResource::collection($facilities)->additional([
            'message' => 'Daftar fasilitas berhasil diambil.',
            'status' => 'success'
        ]);
    }

    public function store(StoreFacilityRequest $request)
    {
        $facility = $this->facilityService->createFacility($request->validated());
        return $this->successResponse(new FacilityResource($facility), 'Fasilitas berhasil ditambahkan.', 201);
    }

    public function show($id)
    {
        $facility = $this->facilityService->getFacilityById($id);
        return $this->successResponse(new FacilityResource($facility), 'Detail fasilitas berhasil diambil.');
    }

    public function update(UpdateFacilityRequest $request, $id)
    {
        $facility = $this->facilityService->updateFacility($id, $request->validated());
        return $this->successResponse(new FacilityResource($facility), 'Fasilitas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->facilityService->deleteFacility($id);
        return $this->successResponse(null, 'Fasilitas berhasil dihapus.');
    }
}
