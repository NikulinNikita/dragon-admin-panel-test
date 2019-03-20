<?php namespace App\Http\Middleware;

use App\Models\BaseModel;
use App\Models\DepositRequest;
use App\Models\Setting;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class AdminSettings
{

    public function handle(Request $request, Closure $next)
    {
        if (preg_match('/\/admin_panel/', $request->url())) {
            if ( ! session()->has('admin.locale')) {
                $locales = config()->get('translatable.locales');
                session()->put('admin.locale', 'ru');
            }
            app()->setLocale(session()->get('admin.locale'));

            $ui_unblocking_time = Setting::where('key', 'ui_unblocking_time')->first();
            session()->put('admin.settings.ui_unblocking_time', $ui_unblocking_time ? $ui_unblocking_time->value : null);

            BaseModel::setCurrencyToSession(null);

            $this->setBadgesValuesToSession();
        }

        return $next($request);
    }

    protected function setBadgesValuesToSession()
    {
        $user                = auth()->user();
        $roles               = $user ? $user->roles->pluck('name')->all() : [];
        $statuses            = in_array('operator', $roles) ? ['new', 'approved', 'sent_to_recheck_to_operator'] :
            ['new', 'approved_to_proceed', 'sent_to_recheck_to_manager'];
        $todaysRegistrations = User::forTodayOnly()->count();
        $depositRequests     = DepositRequest::whereIn('status', $statuses)->count();
        if (in_array('operator', $roles)) {
            $withdrawalRequests = WithdrawalRequest::where(function ($q1) use ($statuses) {
                $q1->where('created_at', '>=', Carbon::now()->subDay())->where('created_at', '<=', Carbon::now())->whereIn('status', ['succeed']);
            })->orWhereIn('status', $statuses)->count();
        } else {
            $withdrawalRequests = WithdrawalRequest::whereIn('status', $statuses)->count();
        }

        $arr = [
            'todaysRegistrations' => $todaysRegistrations,
            'totalRequests'       => $depositRequests + $withdrawalRequests,
            'depositRequests'     => $depositRequests,
            'withdrawalRequests'  => $withdrawalRequests
        ];

        session()->put('admin.menu.badges', $arr);
    }
}