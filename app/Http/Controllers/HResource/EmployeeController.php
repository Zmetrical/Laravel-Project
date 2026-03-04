<?php

namespace App\Http\Controllers\HResource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        return view('hresource.employees.show');
    }

    public function create()
    {
        return view('hresource.employees.create');
    }
}
