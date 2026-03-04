<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MonthPayController extends Controller
{
    public function index()
    {
        return view('accounting.month_pay');
    }
}
