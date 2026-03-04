<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PayrollManagementController extends Controller
{
    public function index()
    {
        return view('accounting.payroll.show');
    }
}
