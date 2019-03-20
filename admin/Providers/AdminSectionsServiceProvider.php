<?php

namespace Admin\Providers;

use Illuminate\Routing\Router;
use SleepingOwl\Admin\Contracts\Navigation\NavigationInterface;
use SleepingOwl\Admin\Contracts\Template\MetaInterface;
use SleepingOwl\Admin\Contracts\Widgets\WidgetsRegistryInterface;
use SleepingOwl\Admin\Providers\AdminSectionsServiceProvider as ServiceProvider;

class AdminSectionsServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $widgets = [
        \Admin\Widgets\DashboardMap::class,
        \Admin\Widgets\NavigationNotifications::class,
        \Admin\Widgets\NavigationUserBlock::class,
    ];

    /**
     * @var array
     */
    protected $sections = [
        'App\Models\Staff'                         => 'Admin\Http\Sections\Staff',
        'App\Models\User'                          => 'Admin\Http\Sections\Users',
        'App\Models\Role'                          => 'Admin\Http\Sections\Roles',
        'App\Models\Permission'                    => 'Admin\Http\Sections\Permissions',
        'App\Models\Game'                          => 'Admin\Http\Sections\Games',
        'App\Models\Table'                         => 'Admin\Http\Sections\Tables',
        'App\Models\Till'                          => 'Admin\Http\Sections\Tills',
        'App\Models\Currency'                      => 'Admin\Http\Sections\Currencies',
        'App\Models\UserTill'                      => 'Admin\Http\Sections\UserTills',
        'App\Models\Gateway'                       => 'Admin\Http\Sections\Gateways',
        'App\Models\DepositRequest'                => 'Admin\Http\Sections\DepositRequests',
        'App\Models\WithdrawalRequest'             => 'Admin\Http\Sections\WithdrawalRequests',
        'App\Models\Note'                          => 'Admin\Http\Sections\Notes',
        'App\Models\Setting'                       => 'Admin\Http\Sections\Settings',
        'App\Models\Bonus'                         => 'Admin\Http\Sections\Bonuses',
        'App\Models\UserBonus'                     => 'Admin\Http\Sections\UserBonuses',
        'App\Models\UserBonusUsedBet'              => 'Admin\Http\Sections\UserBonusUsedBets',
        'App\Models\BonusLimit'                    => 'Admin\Http\Sections\BonusLimits',
        'App\Models\UserBonusLimit'                => 'Admin\Http\Sections\UserBonusLimits',
        'App\Models\Operation'                     => 'Admin\Http\Sections\Operations',
        'App\Models\UserAuthorization'             => 'Admin\Http\Sections\UserAuthorizations',
        'App\Models\GatewayCurrency'               => 'Admin\Http\Sections\GatewayCurrencies',
        'App\Models\Bank'                          => 'Admin\Http\Sections\Banks',
        'App\Models\BankAccount'                   => 'Admin\Http\Sections\BankAccounts',
        'App\Models\Region'                        => 'Admin\Http\Sections\Regions',
        'App\Models\Agent'                         => 'Admin\Http\Sections\Agents',
        'App\Models\AgentLink'                     => 'Admin\Http\Sections\AgentLinks',
        'App\Models\ExchangeRate'                  => 'Admin\Http\Sections\ExchangeRates',
        'App\Models\Nominal'                       => 'Admin\Http\Sections\Nominals',
        'App\Models\NominalValue'                  => 'Admin\Http\Sections\NominalValues',
        'App\Models\UserStatus'                    => 'Admin\Http\Sections\UserStatuses',
        'App\Models\UserStatusLimit'               => 'Admin\Http\Sections\UserStatusLimits',
        'App\Models\UserStatusPoint'               => 'Admin\Http\Sections\UserStatusPoints',
        'App\Models\Notification'                  => 'Admin\Http\Sections\Notifications',
        'App\Models\UserBank'                      => 'Admin\Http\Sections\UserBanks',
        'App\Models\UserBankAccount'               => 'Admin\Http\Sections\UserBankAccounts',
        'App\Models\TableLimit'                    => 'Admin\Http\Sections\TableLimits',
        'App\Models\TableLimitCurrency'            => 'Admin\Http\Sections\TableLimitCurrencies',
        'App\Models\BankAccountOperation'          => 'Admin\Http\Sections\BankAccountOperations',
        'App\Models\UserBankAccountOperation'      => 'Admin\Http\Sections\UserBankAccountOperations',
        'App\Models\UserStatusChange'              => 'Admin\Http\Sections\UserStatusChanges',
        'App\Models\BaccaratBet'                   => 'Admin\Http\Sections\BaccaratBets',
        'App\Models\StaffSession'                  => 'Admin\Http\Sections\StaffSessions',
        'App\Models\Risk'                          => 'Admin\Http\Sections\Risks',
        'App\Models\RiskEvent'                     => 'Admin\Http\Sections\RiskEvents',
        'App\Models\RiskEventStaffAction'          => 'Admin\Http\Sections\RiskEventStaffActions',
        'App\Models\ReportsBaccaratBet'            => 'Admin\Http\Sections\ReportsBaccaratBets',
        'App\Models\RouletteBet'                   => 'Admin\Http\Sections\RouletteBets',
        'App\Models\ReportsRouletteBet'            => 'Admin\Http\Sections\ReportsRouletteBets',
        'App\Models\InternalOperation'             => 'Admin\Http\Sections\InternalOperations',
        'App\Models\UserSession'                   => 'Admin\Http\Sections\UserSessions',
        'App\Models\ReportsUserFinance'            => 'Admin\Http\Sections\ReportsUserFinances',
        'App\Models\ReportsDealerShift'            => 'Admin\Http\Sections\ReportsDealerShifts',
        'App\Models\BaccaratRound'                 => 'Admin\Http\Sections\BaccaratRounds',
        'App\Models\RouletteRound'                 => 'Admin\Http\Sections\RouletteRounds',
        'App\Models\DepositRequestStatusChange'    => 'Admin\Http\Sections\DepositRequestStatusChanges',
        'App\Models\WithdrawalRequestStatusChange' => 'Admin\Http\Sections\WithdrawalRequestStatusChanges',
        'App\Models\Holiday'                       => 'Admin\Http\Sections\Holidays',
        'App\Models\ResultLimitCurrency'           => 'Admin\Http\Sections\ResultLimitCurrencies',
        'App\Models\BaccaratResult'                => 'Admin\Http\Sections\BaccaratResults',
        'App\Models\RouletteResult'                => 'Admin\Http\Sections\RouletteResults',
        'App\Models\AgentReward'                   => 'Admin\Http\Sections\AgentRewards',
        'App\Models\AgentRewardBet'                => 'Admin\Http\Sections\AgentRewardBets',
        'App\Models\BaccaratShoe'                  => 'Admin\Http\Sections\BaccaratShoes',
        'App\Models\LoopCommandEvent'              => 'Admin\Http\Sections\LoopCommandEvents',
        'App\Models\BonusReward'                   => 'Admin\Http\Sections\BonusRewards',
        'App\Models\BonusRewardBet'                => 'Admin\Http\Sections\BonusRewardBets',
        'App\Models\BetsBankAccrual'               => 'Admin\Http\Sections\BetsBankAccruals',

        'Admin\Http\Sections\PageModels\AgentBet'        => 'Admin\Http\Sections\AgentBets',
        'Admin\Http\Sections\PageModels\BetsBonusAmount' => 'Admin\Http\Sections\BetsBonusAmounts',
        'Admin\Http\Sections\PageModels\McChat'          => 'Admin\Http\Sections\McChats',
    ];

    /**
     * @param \SleepingOwl\Admin\Admin $admin
     *
     * @return void
     */
    public function boot(\SleepingOwl\Admin\Admin $admin)
    {
        $this->loadViewsFrom(base_path("admin/resources/views"), 'admin');
        $this->registerPolicies('Admin\\Policies\\');

        $this->app->call([$this, 'registerRoutes']);
        $this->app->call([$this, 'registerNavigation']);

        parent::boot($admin);

        $this->app->call([$this, 'registerViews']);
        $this->app->call([$this, 'registerMediaPackages']);
    }

    /**
     * @param NavigationInterface $navigation
     */
    public function registerNavigation(NavigationInterface $navigation)
    {
        require base_path('admin/navigation.php');
    }

    /**
     * @param WidgetsRegistryInterface $widgetsRegistry
     */
    public function registerViews(WidgetsRegistryInterface $widgetsRegistry)
    {
        foreach ($this->widgets as $widget) {
            $widgetsRegistry->registerWidget($widget);
        }
    }

    /**
     * @param Router $router
     */
    public function registerRoutes(Router $router)
    {
        $router->group([
            'prefix'     => config('sleeping_owl.url_prefix'),
            'middleware' => config('sleeping_owl.middleware')
        ], function ($router) {
            require base_path('admin/routes.php');
        });
    }

    /**
     * @param MetaInterface $meta
     */
    public function registerMediaPackages(MetaInterface $meta)
    {
        $packages = $meta->assets()->packageManager();

        require base_path('admin/assets.php');
    }

    /**
     * @param string|null $namespace
     *
     * @return array
     */
    public function policies($namespace = null)
    {
        if (is_null($namespace)) {
            $namespace = config('sleeping_owl.policies_namespace', '\\App\\Policies\\');
        }

        $policies         = [];
        $preparedPolicies = collect($this->policies);

        foreach ($this->sections as $model => $section) {
            if ($preparedPolicies->has($section)) {
                $policies[$section] = $preparedPolicies->get($section);
                continue;
            }

            $policyFileExist    = file_exists(str_replace('\\', '/', (__DIR__ . '/../Policies/' . class_basename($section) . 'SectionModelPolicy.php')));
            $policySection      = $policyFileExist ? class_basename($section) : "Base";
            $policies[$section] = "{$namespace}{$policySection}SectionModelPolicy";
        }

        return $policies;
    }
}
