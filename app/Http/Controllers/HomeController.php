<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Display the landing page.
     * If user is authenticated and has permission, redirect to dashboard.
     */
    public function index()
    {
        // If user is authenticated and has dashboard permission, redirect to dashboard
        if (Auth::check() && has_permission('view_dashboard')) {
            return redirect()->route('dashboard');
        }

        // Show landing page for unauthenticated users or users without permission
        return view('home.landing');
    }
}
