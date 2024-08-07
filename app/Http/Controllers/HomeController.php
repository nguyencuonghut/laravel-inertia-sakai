<?php

namespace App\Http\Controllers;
use Inertia\Inertia;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return Inertia::render('Home', [
            'user' => $user,
        ]);
    }

    public function charts()
    {
        return Inertia::render('Charts');
    }
}
