<?php

namespace App\Actions\SupplierPayment;

use App\Models\SupplierPayment;
use App\Models\SupplierPaymentItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CreateSupplierPayment
{
    /**
     * Execute the action to create a Supplier Payment.
     *
     * @param  array<string, mixed>  $data
     * @return SupplierPayment
     */
    public function execute(array $data): SupplierPayment
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];

            // 1. Create the base record as draft
            $payment = SupplierPayment::create([
                'payment_no' => $data['payment_no'] ?? SupplierPayment::generatePaymentNo(),
                'payment_date' => $data['payment_date'],
                'supplier_id' => $data['supplier_id'],
                'branch_id' => $data['branch_id'],
                'account_id' => $data['account_id'],
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'total_amount' => 0, // Will be updated from items sum
                'notes' => $data['notes'] ?? null,
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

            // 2. Create items
            $totalPaid = 0;
            foreach ($items as $item) {
                if (!($item['is_selected'] ?? false)) {
                    continue;
                }

                $amountPaid = intval($item['amount_paid'] ?? 0);
                if ($amountPaid <= 0) {
                    continue;
                }

                SupplierPaymentItem::create([
                    'supplier_payment_id' => $payment->id,
                    'purchase_transaction_id' => $item['purchase_transaction_id'],
                    'amount_due' => intval($item['amount_due'] ?? 0),
                    'amount_paid' => $amountPaid,
                ]);

                $totalPaid += $amountPaid;
            }

            // 3. Update total amount in header
            $payment->update([
                'total_amount' => $totalPaid,
            ]);

            // 4. Process status transition if requested
            if (($data['status'] ?? 'draft') === 'posted') {
                app(ProcessSupplierPaymentComplete::class)->execute($payment);
            }

            return $payment;
        });
    }
}
