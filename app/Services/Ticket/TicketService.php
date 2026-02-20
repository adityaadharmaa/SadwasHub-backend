<?php

namespace App\Services\Ticket;

use App\Models\Booking;
use App\Repositories\Interfaces\TicketRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Validation\ValidationException;

class TicketService extends BaseService
{
    protected $ticketRepo;
    public function __construct(TicketRepositoryInterface $ticketRepo)
    {
        $this->ticketRepo = $ticketRepo;
    }

    public function getTenantTickets($userId, int $perPage = 10)
    {
        return $this->ticketRepo->getByUser($userId, $perPage);
    }

    public function getAllTickets(array $filters = [], int $perPage = 10)
    {
        return $this->ticketRepo->getAllWithFilters($filters, $perPage);
    }

    public function createTicket($user, array $data)
    {
        // 1. Cari kamar yang sedang aktif disewa oleh user
        $activeBooking = Booking::where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->where('check_in_date', '<=', now())
            ->where('check_out_date', '>=', now())
            ->first();

        if (!$activeBooking) {
            throw ValidationException::withMessages(['room' => 'Anda tidak memiliki kamar aktif saat ini untuk dilaporkan.']);
        }

        // 2. Proses upload foto jika ada 
        if (isset($data['photo'])) {
            $path = $data['photo']->store('ticket_photos', 'public');
            $data['photo_path'] = $path;
            unset($data['photo']);
        }

        // 3. Simpan ticket dengan ID kamar yang terdeteksi otomatis
        $data['user_id'] = $user->id;
        $data['room_id'] = $activeBooking->room_id;
        $data['status'] = 'open';

        return $this->atomic(function () use ($data) {
            $ticket = $this->ticketRepo->create($data);
            return $ticket->load(['room.type']);
        });
    }

    public function updateTicketStatus($id, array $data)
    {
        return $this->atomic(function () use ($id, $data) {
            $ticket = $this->ticketRepo->find($id);

            if (!$ticket) {
                throw ValidationException::withMessages(['id' => 'Data komplain tidak ditemukan. Pastikan ID valid.']);
            }

            $this->ticketRepo->update($id, $data);

            return $ticket->fresh()->load(['user.profile', 'room.type']);
        });
    }
}
