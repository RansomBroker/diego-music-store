<?php

namespace App\Actions\SupplierPayment;

use App\Models\SupplierPayment;
use App\Models\SupplierPaymentItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UpdateSupplierPayment
{
    /**
     * Execute the action to update a Supplier Payment.
     *
     * @param  SupplierPayment  $payment
     * @param  array<string, mixed>  $data
     * @return SupplierPayment
     */
    public function execute(SupplierPayment $payment, array $data): SupplierPayment
    {
        return DB::transaction(function () use ($payment, $data) {
            // Guard clause: status must be draft
            if ($payment->status !== 'draft') {
                throw new InvalidArgumentException('Pelunasan hutang yang sudah diposting tidak dapat diubah.');
            }

            // 1. Update header details
            $payment->update([
                'payment_date' => $data['payment_date'] ?? $payment->payment_date,
                'supplier_id' => $data['supplier_id'] ?? $payment->supplier_id,
                'branch_id' => $data['branch_id'] ?? $payment->branch_id,
                'account_id' => $data['account_id'] ?? $payment->account_id,
                'payment_method' => $data['payment_method'] ?? $payment->payment_method,
                'payment_reference' => $data['payment_reference'] ?? $payment->payment_reference,
                'notes' => $data['notes'] ?? $payment->notes,
            ]);

            // 2. Sync items (delete existing and recreate)
            if (isset($data['items'])) {
                $payment->items()->delete();

                $totalPaid = 0;
                foreach ($data['items'] as $item) {
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

                // Update total amount in header
                $payment->update([
                    'total_amount' => $totalPaid,
                ]);
            }

            // 3. Process status transition if requested
            if (($data['status'] ?? 'draft') === 'posted') {
                app(ProcessSupplierPaymentComplete::class)->execute($payment);
            }

            return $payment;
        });
    }
}
