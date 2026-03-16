<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $pendingRequests = ProfileUpdateRequest::where('employeeId', $user->id)
            ->where('status', 'pending')
            ->orderByDesc('submittedDate')
            ->get();

        $recentApproved = ProfileUpdateRequest::where('employeeId', $user->id)
            ->where('status', 'approved')
            ->where('reviewDate', '>=', now()->subHours(48))
            ->orderByDesc('reviewDate')
            ->get();

        return view('employee.profile', compact('user', 'pendingRequests', 'recentApproved'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'field'     => ['required', 'in:email,phone,civilStatus,address'],
            'new_value' => ['required', 'string', 'max:1000'],
            'reason'    => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $alreadyPending = ProfileUpdateRequest::where('employeeId', $user->id)
            ->where('field', $request->field)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            return back()
                ->withInput()
                ->with('error', 'You already have a pending request for this field. Please wait for HR to review it.');
        }

        $oldValue = match ($request->field) {
            'email'       => $user->email,
            'phone'       => $user->phoneNumber,
            'civilStatus' => $user->civilStatus,
            'address'     => collect([
                                $user->addressStreet,
                                $user->addressBarangay,
                                $user->addressCity,
                                $user->addressProvince,
                                $user->addressRegion,
                                $user->addressZipCode,
                            ])->filter()->implode(', '),
            default       => null,
        };

        ProfileUpdateRequest::create([
            'id'            => 'PRU-' . strtoupper(Str::random(8)),
            'employeeId'    => $user->id,
            'employeeName'  => $user->fullName,
            'field'         => $request->field,
            'oldValue'      => $oldValue,
            'newValue'      => $request->new_value,
            'reason'        => $request->reason,
            'status'        => 'pending',
            'submittedDate' => now(),
        ]);

        return back()->with('success', 'Your request has been submitted. HR will review it shortly.');
    }
}