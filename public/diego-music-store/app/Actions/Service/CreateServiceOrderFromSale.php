<?php

namespace App\Actions\Service;

use App\Models\Sale;
use App\Models\ServiceOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CreateServiceOrderFromSale
{
    /**
     * Execute Service Order creation automatically when a POS sale contains service items.
     *
     * @param Sale $sale
     * @return array Array of created ServiceOrder models
     */
    public static function execute(Sale $sale): array
    {
        $createdOrders = [];

        DB::transaction(function () use ($sale, &$createdOrders) {
            foreach ($sale->items as $item) {
                $variant = $item->variant;
                $product = $variant?->product;

                // Check if product is marked as service
                if ($product && $product->isService()) {
                    // Generate unique ticket code: SVC-YYYYMMDD-XXXX
                    $datePrefix = Carbon::now()->format('Ymd');
                    $countToday = ServiceOrder::whereDate('created_at', Carbon::today())->count() + 1;
                    $ticketCode = sprintf('SVC-%s-%04d', $datePrefix, $countToday);

                    $deviceName = $product->name;
                    if ($variant && !empty($variant->name) && $variant->name !== 'Standard') {
                        $deviceName .= ' (' . $variant->name . ')';
                    }

                    $serviceOrder = ServiceOrder::create([
                        'ticket_code'         => $ticketCode,
                        'sale_id'             => $sale->id,
                        'branch_id'           => $sale->branch_id,
                        'customer_id'         => $sale->customer_id,
                        'customer_name'       => $sale->customer->name ?? 'Pelanggan Umum',
                        'customer_phone'      => $sale->customer->phone ?? null,
                        'device_name'         => $deviceName,
                        'serial_number'       => null,
                        'complaint'           => $item->notes ?: 'Jasa Service dari Transaksi Kasir POS (' . $sale->invoice_number . ')',
                        'technician_id'       => $sale->sales_rep_id ?: $sale->created_by,
                        'status'              => 'received',
                        'estimated_cost'      => floatval($item->total_price),
                        'total_cost'          => floatval($item->total_price),
                        'notes'               => 'Otomatis di-generate dari transaksi POS #' . $sale->invoice_number,
                        'created_by'          => $sale->created_by,
                    ]);

                    $createdOrders[] = $serviceOrder;
                }
            }
        });

        return $createdOrders;
    }
}
