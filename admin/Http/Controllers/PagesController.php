<?php

namespace Admin\Http\Controllers;

use App\Models\BaseModel;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function switchLocale(Request $request)
    {
        session()->put('admin.locale', $request->get('newLocale'));
        redirect()->back()->send();
    }

    public function switchCurrency(Request $request)
    {
        BaseModel::setCurrencyToSession($request->get('currency_id'));
        redirect()->back()->send();
    }

    public function search(Request $request)
    {
        $searchParameters = [];
        foreach ($request->all() as $k => $v) {
            if ($v && $k !== '_token') {
                $searchParameters[$k] = $v;
            }
        }
        $previousUrl = strpos(url()->previous(), '?') ? stristr(url()->previous(), '?', true) : url()->previous();

        return redirect()->to($previousUrl . '?' . http_build_query($searchParameters));
    }
}