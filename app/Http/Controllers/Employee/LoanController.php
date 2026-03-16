<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\employee\Loan;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Load all loans with payment count and sum in one query using withCount + withSum
        $loans = Loan::where('user_id', $userId)
            ->withCount('payments')
            ->withSum('payments', 'amount')
            ->orderByDesc('start_date')
            ->get();

        // Transform to plain array for the blade/JS layer
        $loansData = $loans->map(function (Loan $loan) {
            $totalPaid        = (float) ($loan->payments_sum_amount ?? 0);
            $remainingBalance = max(0, (float) $loan->amount - $totalPaid);

            return [
                'id'                   => $loan->id,
                'loan_type'            => $loan->loan_type,
                'loan_type_name'       => $loan->loan_type_name,
                'amount'               => (float) $loan->amount,
                'monthly_amortization' => (float) $loan->monthly_amortization,
                'term_months'          => $loan->term_months,
                'payments_made'        => $loan->payments_count,
                'remaining_balance'    => $remainingBalance,
                'start_date'           => $loan->start_date?->toDateString(),
                'completed_date'       => $loan->completed_date?->toDateString(),
                'status'               => $loan->status,
                'notes'                => $loan->notes,
            ];
        })->values()->toArray();

        return view('employee.loan', compact('loansData'));
    }
}