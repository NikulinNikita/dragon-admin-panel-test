<?php

namespace App\Http\Controllers;

class PagesController extends Controller
{
    public function index()
    {
        return redirect('/admin_panel');
    }

    public function home()
    {
        if (auth()->guest()) {
            return view('welcome');
        }

        return redirect('/admin_panel');
    }

    public function timer()
    {
        return view('timer');
    }

    public function tables()
    {
        return view('manager.tables');
    }

    public function broadcast()
    {
        return view('manager.broadcast');
    }
}
