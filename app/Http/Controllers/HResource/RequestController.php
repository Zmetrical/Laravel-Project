<?php

namespace App\Http\Controllers\HResource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function index()
    {
        return view('hresource.requests.show');
    }
}
