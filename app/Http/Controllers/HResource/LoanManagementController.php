<?php

namespace App\Http\Controllers\HResource;

use App\Http\Controllers\Controller;
use App\Models\employee\Loan;
use App\Models\employee\LoanPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LoanManagementController extends Controller
{
    // =========================================================================
    // PAGE
    // =========================================================================

    public function index()
    {
        return view('hresource.loans.show');
    }

    // =========================================================================
    // STATS
    // =========================================================================

    public function stats(): JsonResponse
    {
        $active = Loan::active()->withCount('payments')->get();

        // Derive remaining balance per active loan from last payment
        $totalBalance = $active->sum(function ($loan) {
            $last = $loan->payments()->latest('payment_date')->first();
            return $last ? (float) $last->balance_after : (float) $loan->amount;
        });

        return response()->json([
            'active'        => $active->count(),
            'completed'     => Loan::completed()->count(),
            'sss'           => Loan::active()->where('loan_type', 'sss')->count(),
            'pagibig'       => Loan::active()->where('loan_type', 'pagibig')->count(),
            'total_balance' => number_format($totalBalance, 2),
        ]);
    }

    // =========================================================================
    // LIST
    // =========================================================================

    public function list(Request $request): JsonResponse
    {
        $status = $request->query('status', 'active'); // active | completed | all
        $type   = $request->query('type', 'all');      // sss | pagibig | all
        $search = $request->query('search', '');

        $query = Loan::with(['employee', 'payments' => function ($q) {
            $q->latest('payment_date')->limit(1);
        }]);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($type !== 'all') {
            $query->where('loan_type', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($eq) use ($search) {
                      $eq->where('fullName', 'like', "%{$search}%")
                         ->orWhere('id', 'like', "%{$search}%");
                  })
                  ->orWhere('loan_type_name', 'like', "%{$search}%");
            });
        }

        $records = $query->orderByDesc('created_at')->get()->map(function ($loan) {
            $paymentsMade     = $loan->payments_count ?? $loan->payments->count();
            $lastPayment      = $loan->payments->first();
            $remainingBalance = $lastPayment
                ? (float) $lastPayment->balance_after
                : (float) $loan->amount;

            return [
                'id'                   => $loan->id,
                'employee_id'          => $loan->user_id,
                'employee'             => $loan->employee?->fullName ?? '—',
                'loan_type'            => $loan->loan_type,
                'loan_type_name'       => $loan->loan_type_name,
                'amount'               => number_format($loan->amount, 2),
                'monthly_amortization' => number_format($loan->monthly_amortization, 2),
                'term_months'          => $loan->term_months,
                'payments_made'        => $paymentsMade,
                'remaining_balance'    => number_format($remainingBalance, 2),
                'progress_percent'     => $loan->term_months > 0
                    ? min(100, (int) round(($paymentsMade / $loan->term_months) * 100))
                    : 0,
                'start_date'           => $loan->start_date?->format('M d, Y'),
                'completed_date'       => $loan->completed_date?->format('M d, Y'),
                'status'               => $loan->status,
                'encoded_by'           => $loan->encodedBy?->fullName ?? '—',
                'created_at'           => $loan->created_at?->format('M d, Y'),
            ];
        });

        return response()->json($records);
    }

    // =========================================================================
    // EMPLOYEE SEARCH (for add modal dropdown)
    // =========================================================================

    public function employees(Request $request): JsonResponse
    {
        $search = $request->query('q', '');

        $employees = User::active()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('fullName',   'like', "%{$search}%")
                          ->orWhere('id',        'like', "%{$search}%")
                          ->orWhere('department','like', "%{$search}%");
                });
            })
            ->orderBy('fullName')
            ->limit(15)
            ->get(['id', 'fullName', 'department', 'position'])
            ->map(fn ($u) => [
                'id'         => $u->id,
                'full_name'  => $u->fullName,
                'department' => $u->department ?? '—',
                'position'   => $u->position   ?? '—',
            ]);

        return response()->json($employees);
    }

    // =========================================================================
    // SHOW (view modal)
    // =========================================================================

    public function show(Loan $loan): JsonResponse
    {
        $loan->load(['employee', 'encodedBy', 'payments' => function ($q) {
            $q->orderByDesc('payment_date')->limit(6);
        }]);

        $paymentsMade     = $loan->payments->count();
        $lastPayment      = $loan->payments->first();
        $remainingBalance = $lastPayment
            ? (float) $lastPayment->balance_after
            : (float) $loan->amount;

        // Next payment date
        $nextPaymentDate = null;
        if ($loan->isActive() && $remainingBalance > 0 && $loan->start_date) {
            $next = $loan->start_date->copy()->addMonths($paymentsMade);
            $nextPaymentDate = $next->format('M d, Y');
        }

        return response()->json([
            'id'                   => $loan->id,
            'employee'             => $loan->employee?->fullName ?? '—',
            'employee_id'          => $loan->user_id,
            'loan_type'            => $loan->loan_type,
            'loan_type_name'       => $loan->loan_type_name,
            'amount'               => number_format($loan->amount, 2),
            'monthly_amortization' => number_format($loan->monthly_amortization, 2),
            'term_months'          => $loan->term_months,
            'payments_made'        => $paymentsMade,
            'remaining_balance'    => number_format($remainingBalance, 2),
            'progress_percent'     => $loan->term_months > 0
                ? min(100, (int) round(($paymentsMade / $loan->term_months) * 100))
                : 0,
            'start_date'           => $loan->start_date?->format('M d, Y'),
            'completed_date'       => $loan->completed_date?->format('M d, Y'),
            'status'               => $loan->status,
            'next_payment_date'    => $nextPaymentDate,
            'encoded_by'           => $loan->encodedBy?->fullName ?? '—',
            'created_at'           => $loan->created_at?->format('M d, Y'),
            'notes'                => $loan->notes,
            'recent_payments'      => $loan->payments->map(fn ($p) => [
                'date'          => $p->payment_date?->format('M d, Y'),
                'amount'        => number_format($p->amount, 2),
                'balance_after' => number_format($p->balance_after, 2),
                'type'          => $p->payment_type,
            ]),
        ]);
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id'              => ['required', 'string', 'exists:users,id'],
            'loan_type'            => ['required', Rule::in(['sss', 'pagibig'])],
            'amount'               => ['required', 'numeric', 'min:1'],
            'monthly_amortization' => ['required', 'numeric', 'min:1'],
            'term_months'          => ['required', 'integer', 'min:1', 'max:120'],
            'start_date'           => ['required', 'date'],
            'notes'                => ['nullable', 'string', 'max:500'],
        ]);

        $loanTypeName = $validated['loan_type'] === 'sss' ? 'SSS Loan' : 'PAG-IBIG Loan';

        $loan = Loan::create([
            ...$validated,
            'loan_type_name' => $loanTypeName,
            'status'         => 'active',
            'encoded_by'     => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Loan added successfully.',
            'id'      => $loan->id,
        ], 201);
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function update(Request $request, Loan $loan): JsonResponse
    {
        if (! $loan->isActive()) {
            return response()->json(['message' => 'Only active loans can be edited.'], 422);
        }

        $validated = $request->validate([
            'amount'               => ['required', 'numeric', 'min:1'],
            'monthly_amortization' => ['required', 'numeric', 'min:1'],
            'term_months'          => ['required', 'integer', 'min:1', 'max:120'],
            'start_date'           => ['required', 'date'],
            'notes'                => ['nullable', 'string', 'max:500'],
        ]);

        $loan->update($validated);

        return response()->json(['message' => 'Loan updated successfully.']);
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function destroy(Loan $loan): JsonResponse
    {
        if ($loan->payments()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a loan that already has payment records.',
            ], 422);
        }

        $loan->delete();

        return response()->json(['message' => 'Loan deleted successfully.']);
    }
}