<?php

namespace Admin\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switchLocale(Request $request)
    {
        session()->put('admin.locale', $request->get('newLocale'));
        \Redirect::back()->send();
    }
}
