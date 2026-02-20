<?php

namespace App\Http\Controllers\Room;

use App\Http\Controllers\Controller;
use App\Http\Requests\Room\StoreRoomTypeRequest;
use App\Http\Requests\Room\UpdateRoomTypesRequest;
use App\Http\Resources\RoomTypeResource;
use App\Services\Room\RoomTypeService;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    protected $roomTypeService;

    public function __construct(RoomTypeService $roomTypeService)
    {
        $this->roomTypeService = $roomTypeService;
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);

        $types = $this->roomTypeService->getAllRoomTypes($search, $perPage);

        return RoomTypeResource::collection($types)->additional([
            'message' => 'Daftar tipe kamar berhasil diambil.',
            'status' => 'success'
        ]);
    }

    public function store(StoreRoomTypeRequest $request)
    {
        $types = $this->roomTypeService->createRoomType($request->validated());

        return $this->successResponse(
            new RoomTypeResource($types),
            'Tipe kamar berhasil ditambahkan.',
            201
        );
    }

    public function show($id)
    {
        $roomType = $this->roomTypeService->getRoomTypeById($id);

        return $this->successResponse(
            new RoomTypeResource($roomType),
            'Detail tipe kamar berhasil diambil.'
        );
    }

    public function update(UpdateRoomTypesRequest $request, $id)
    {
        $roomType = $this->roomTypeService->updateRoomType($id, $request->validated());

        return $this->successResponse(
            new RoomTypeResource($roomType),
            'Tipe kamar berhasil diperbarui.'
        );
    }

    public function destroy($id)
    {
        $this->roomTypeService->deleteRoomType($id);
        return $this->successResponse(null, 'Tipe kamar berhasil dihapus.');
    }
}
