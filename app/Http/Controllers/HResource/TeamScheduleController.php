<?php

namespace App\Http\Controllers\HResource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TeamScheduleController extends Controller
{
    public function index()
    {
        return view('hresource.team.schedule');
    }
}
