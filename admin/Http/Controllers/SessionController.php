<?php

namespace Admin\Http\Controllers;

use App\Console\Commands\FetchExchangeRates;
use App\Models\BaseModel;
use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function fetchCurrencies(Request $request)
    {
        (new FetchExchangeRates())->handle();
        BaseModel::setCurrencyToSession(session()->get('admin.currency'));

        return redirect()->back()->with(['success_message' => '<i class="fa fa-check fa-lg"></i> Currencies have been fetched successfully']);
    }
}
