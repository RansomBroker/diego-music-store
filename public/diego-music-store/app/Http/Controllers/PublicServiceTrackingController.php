<?php

namespace App\Http\Controllers;

use App\Models\ServiceOrder;
use Illuminate\Http\Request;

class PublicServiceTrackingController extends Controller
{
    /**
     * Show public tracking status page for a service ticket.
     *
     * @param string $ticketCode
     */
    public function show(string $ticketCode)
    {
        $serviceOrder = ServiceOrder::with(['branch', 'technician', 'sale'])
            ->where('ticket_code', $ticketCode)
            ->firstOrFail();

        // Mask phone number for privacy: 0812345678 -> 0812****5678
        $maskedPhone = '-';
        if (!empty($serviceOrder->customer_phone)) {
            $phone = $serviceOrder->customer_phone;
            $len = strlen($phone);
            if ($len > 6) {
                $maskedPhone = substr($phone, 0, 4) . str_repeat('*', max(2, $len - 6)) . substr($phone, -2);
            } else {
                $maskedPhone = $phone;
            }
        }

        return view('public-service-tracking', [
            'so'          => $serviceOrder,
            'maskedPhone' => $maskedPhone,
        ]);
    }
}
