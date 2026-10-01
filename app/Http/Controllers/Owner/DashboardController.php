<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function show()
    {
        return view('owner.admin');
    }
}
