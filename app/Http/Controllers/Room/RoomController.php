<?php

namespace App\Http\Controllers\Room;

use App\Http\Controllers\Controller;
use App\Http\Requests\Room\StoreRoomRequest;
use App\Http\Requests\Room\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Services\Room\RoomService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $roomTypeId = $request->query('room_type_id');
        $perPage = $request->query('perPage', 10);

        $rooms = $this->roomService->getAllRooms($search, $status, $roomTypeId, $perPage);
        return RoomResource::collection($rooms)->additional([
            'message' => 'Daftar kamar berhasil diambil.',
            'status' => 'success'
        ]);
    }

    public function store(StoreRoomRequest $request)
    {
        $room = $this->roomService->createRoom($request->validated());
        return $this->successResponse(
            $room,
            'Kamar berhasil ditambahkan.',
            201
        );
    }

    public function show($id)
    {
        $room = $this->roomService->getRoomById($id);
        return $this->successResponse(
            new RoomResource($room),
            'Detail kamar berhasil diambil.'
        );
    }

    public function update(UpdateRoomRequest $request, $id)
    {
        $room = $this->roomService->updateRoom($id, $request->validated());
        return $this->successResponse(
            new RoomResource($room),
            'Kamar berhasil diperbarui.'
        );
    }

    public function destroy($id)
    {
        $this->roomService->deleteRoom($id);
        return $this->successResponse(
            null,
            'Kamar berhasil dihapus.'
        );
    }
}
