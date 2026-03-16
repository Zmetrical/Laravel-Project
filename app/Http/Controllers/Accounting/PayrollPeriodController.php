<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\accounting\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
 
class PayrollPeriodController extends Controller
{
    /**
     * List all payroll periods, newest first.
     */
    public function index()
    {
        $periods = PayrollPeriod::withCount('records')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByRaw("FIELD(period_type, '16th-end', '1st-15th')")
            ->paginate(15);
 
        return view('accounting.payroll.period', compact('periods'));
    }
 
    /**
     * Store a new payroll period.
     * start_date and end_date are auto-computed; only pay_date is user-supplied.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'period_type' => ['required', Rule::in(['1st-15th', '16th-end'])],
            'month'       => ['required', 'integer', 'min:1', 'max:12'],
            'year'        => ['required', 'integer', 'min:2020', 'max:2099'],
            'pay_date'    => ['required', 'date'],
            'notes'       => ['nullable', 'string', 'max:500'],
        ]);
 
        // Prevent duplicate periods
        $exists = PayrollPeriod::where('period_type', $validated['period_type'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->exists();
 
        if ($exists) {
            return back()
                ->withInput()
                ->with('error', 'A payroll period for that month, year, and type already exists.');
        }
 
        $dates = PayrollPeriod::computeDates(
            $validated['period_type'],
            (int) $validated['month'],
            (int) $validated['year']
        );
 
        PayrollPeriod::create(array_merge($validated, $dates));
 
        return back()->with('success', 'Payroll period created successfully.');
    }
 
    /**
     * Advance a period's status through the workflow.
     * Allowed transitions: draft → processing → released → closed
     */
    public function updateStatus(Request $request, PayrollPeriod $period)
    {
        $transitions = [
            'draft'      => 'processing',
            'processing' => 'released',
            'released'   => 'closed',
        ];
 
        if (! array_key_exists($period->status, $transitions)) {
            return back()->with('error', 'This period cannot be advanced further.');
        }
 
        $nextStatus = $transitions[$period->status];
        $userId     = auth()->id();
 
        $update = ['status' => $nextStatus];
 
        if ($nextStatus === 'processing') {
            $update['processed_by'] = $userId;
        }
 
        if ($nextStatus === 'released') {
            // Require at least one record before releasing
            if ($period->records()->count() === 0) {
                return back()->with('error', 'Cannot release a period with no payroll records.');
            }
            $update['released_by'] = $userId;
        }
 
        $period->update($update);
 
        $labels = [
            'processing' => 'Payroll period is now being processed.',
            'released'   => 'Payroll period has been released. Employees can now view their payslips.',
            'closed'     => 'Payroll period has been closed.',
        ];
 
        return back()->with('success', $labels[$nextStatus]);
    }
}