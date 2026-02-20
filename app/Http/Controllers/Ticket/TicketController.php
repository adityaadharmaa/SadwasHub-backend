<?php

namespace App\Http\Controllers\Ticket;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ticket\StoreTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Services\Ticket\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    protected $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    // Tenant Endpoint
    public function myTickets(Request $request)
    {
        $tickets = $this->ticketService->getTenantTickets($request->user()->id, $request->query('per_page', 10));
        return TicketResource::collection($tickets);
    }

    public function store(StoreTicketRequest $request)
    {
        $ticket = $this->ticketService->createTicket($request->user(), $request->validated());
        return $this->successResponse(new TicketResource($ticket), 'Komplain berhasil dikirim', 201);
    }

    // End Tenant Endpoint

    // Admin Endpoint
    public function index(Request $request)
    {
        $filters = [
            'status' => $request->query('status'),
            'priority' => $request->query('priority')
        ];
        $ticket = $this->ticketService->getAllTickets($filters, $request->query('per_page', 10));
        return TicketResource::collection($ticket);
    }

    public function update(UpdateTicketRequest $request, $id)
    {
        $ticket = $this->ticketService->updateTicketStatus($id, $request->validated());
        return $this->successResponse(new TicketResource($ticket), 'Status komplain berhasil diperbarui.');
    }
    // End Admin Endpoint
}
