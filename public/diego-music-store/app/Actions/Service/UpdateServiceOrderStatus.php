<?php

namespace App\Actions\Service;

use App\Models\ServiceOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class UpdateServiceOrderStatus
{
    /**
     * Execute Service Order status & details update.
     *
     * @param ServiceOrder $serviceOrder
     * @param array $data
     * @return ServiceOrder
     */
    public static function execute(ServiceOrder $serviceOrder, array $data): ServiceOrder
    {
        DB::transaction(function () use ($serviceOrder, $data) {
            $updateData = [];

            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
                if (in_array($data['status'], ['completed', 'picked_up']) && empty($serviceOrder->completion_date)) {
                    $updateData['completion_date'] = Carbon::now();
                }
            }

            if (isset($data['technician_id'])) {
                $updateData['technician_id'] = $data['technician_id'];
            }

            if (isset($data['serial_number'])) {
                $updateData['serial_number'] = $data['serial_number'];
            }

            if (isset($data['complaint'])) {
                $updateData['complaint'] = $data['complaint'];
            }

            if (isset($data['notes'])) {
                $updateData['notes'] = $data['notes'];
            }

            if (isset($data['additional_charges'])) {
                $updateData['additional_charges'] = $data['additional_charges'];

                // Calculate total cost
                $additionalSum = 0;
                if (is_array($data['additional_charges'])) {
                    foreach ($data['additional_charges'] as $chg) {
                        $additionalSum += floatval($chg['amount'] ?? 0);
                    }
                }
                $updateData['total_cost'] = floatval($serviceOrder->estimated_cost) + $additionalSum;
            }

            $serviceOrder->update($updateData);
        });

        return $serviceOrder->fresh();
    }
}
