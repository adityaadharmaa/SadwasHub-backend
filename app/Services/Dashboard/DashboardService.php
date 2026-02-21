<?php

namespace App\Services\Dashboard;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Ticket;
use Carbon\Carbon;

class DashboardService
{
    public function getAnalytics()
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // 1. Statistik Kamar & Okupansi
        $totalRooms = Room::count();
        $occupiedRooms = Room::where('status', 'occupied')->count();
        $availableRooms = Room::where('status', 'available')->count();

        // Menghitung persentase kamar yang terisi
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 2) : 0;

        // 2. Pendapatan bulan Ini (Dari tagihan yang sudah lunas)
        $revenueThisMonth = Payment::where('status', 'paid')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('amount');

        // 3. Tiket komplain (Yang butuh perhatian admin)
        $pendingTickets = Ticket::whereIn('status', ['open', 'in_progress'])->count();

        // 4. Booking Aktif (Penyewa yang tinggal saat ini)
        $activeBookings = Booking::where('status', 'confirmed')
            ->where('check_in_date', '<=', $now->toDateString())
            ->where('check_out_date', '>=', $now->toDateString())
            ->count();

        // 5. Grafik tren pendapatan
        $chartLabels = [];
        $chartData = [];

        // Looping dari 5 bulan lalu sampai bulan ini (total 6 bulan)
        for ($i = 5; $i >= 0; $i--) {
            // Mundur $i bulan dari bulan ini
            $date = Carbon::now()->subMonths($i);

            // Format nama bulan 
            $monthLabel = $date->translatedFormat('M Y');

            // Hitung total uang masuk di bulan tersebut
            $monthlyRevenue = Payment::where('status', 'paid')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount');

            // Masukkan ke dalam array untuk frontend  
            $chartLabels[] = $monthLabel;
            $chartData[] = (float) $monthlyRevenue;
        }

        return [
            'rooms' => [
                'total' => $totalRooms,
                'occupied' => $occupiedRooms,
                'available' => $availableRooms,
                'occupancy_rate_percentage' => $occupancyRate
            ],
            'financials' => [
                'revenue_this_month' => (float) $revenueThisMonth,
                'formatted_revenue' => 'Rp. ' . number_format($revenueThisMonth, 0, ',', '.')
            ],
            'tickets' => [
                'requires_action' => $pendingTickets
            ],
            'tenants' => [
                'active_now' => $activeBookings
            ],
            // Data grafik siap pakai untuk frontend
            'charts' => [
                'revenue_trend' => [
                    'labels' => $chartLabels,
                    'data' => $chartData
                ],
            ]
        ];
    }
}
