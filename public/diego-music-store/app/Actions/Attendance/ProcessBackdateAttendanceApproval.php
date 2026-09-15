<?php

namespace App\Actions\Attendance;

use App\Models\AttendanceBackdateRequest;
use App\Models\EmployeeAttendance;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProcessBackdateAttendanceApproval
{
    /**
     * Execute approving or rejecting a backdate attendance request.
     */
    public function execute(AttendanceBackdateRequest $request, string $action, ?User $approver = null, ?string $adminNotes = null): bool
    {
        if ($request->status !== 'pending') {
            throw new InvalidArgumentException('Pengajuan ini telah diproses sebelumnya.');
        }

        if (!in_array($action, ['approve', 'reject'])) {
            throw new InvalidArgumentException('Aksi tidak valid.');
        }

        $approverUser = $approver ?: auth()->user();

        return DB::transaction(function () use ($request, $action, $approverUser, $adminNotes) {
            if ($action === 'approve') {
                $request->update([
                    'status'      => 'approved',
                    'approved_by' => $approverUser?->id,
                    'approved_at' => now(),
                    'admin_notes' => $adminNotes,
                ]);

                $requestedDateStr = Carbon::parse($request->requested_date)->format('Y-m-d');
                $clockInTimeStr  = $requestedDateStr . ' ' . ($request->clock_in ?: '09:00:00');
                $clockOutTimeStr = $request->clock_out ? ($requestedDateStr . ' ' . $request->clock_out) : null;

                EmployeeAttendance::updateOrCreate(
                    [
                        'employee_id' => $request->employee_id,
                        'date'        => $requestedDateStr,
                    ],
                    [
                        'branch_id'             => $request->branch_id,
                        'clock_in'              => $clockInTimeStr,
                        'clock_out'             => $clockOutTimeStr,
                        'status'                => 'hadir',
                        'clock_in_photo_path'   => $request->proof_photo_path,
                        'notes'                 => 'Presensi Backdate (Disetujui Owner)',
                        'is_backdate'           => true,
                        'approved_by'           => $approverUser?->id,
                    ]
                );
            } else {
                $request->update([
                    'status'      => 'rejected',
                    'approved_by' => $approverUser?->id,
                    'approved_at' => now(),
                    'admin_notes' => $adminNotes,
                ]);
            }

            return true;
        });
    }
}
