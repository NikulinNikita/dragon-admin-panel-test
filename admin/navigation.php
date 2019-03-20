<?php

/**
 * @var \SleepingOwl\Admin\Contracts\Navigation\NavigationInterface $navigation
 * @see http://sleepingowladmin.ru/docs/menu_configuration
 */

use Admin\Http\Sections\PageModels\BetsBonusAmount;
use Admin\Http\Sections\PageModels\McChat;
use Admin\Navigation\Page;
use App\Models\Agent;
use App\Models\AgentLink;
use App\Models\AgentReward;
use App\Models\BaccaratBet;
use App\Models\BaccaratRound;
use App\Models\BaccaratShoe;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\BankAccountOperation;
use App\Models\Bonus;
use App\Models\BonusReward;
use App\Models\Currency;
use App\Models\DepositRequest;
use App\Models\ExchangeRate;
use App\Models\Game;
use App\Models\Gateway;
use App\Models\Holiday;
use App\Models\LoopCommandEvent;
use App\Models\Nominal;
use App\Models\Note;
use App\Models\Notification;
use App\Models\Permission;
use App\Models\Region;
use App\Models\ReportsBaccaratBet;
use App\Models\ReportsDealerShift;
use App\Models\ReportsRouletteBet;
use App\Models\ReportsUserFinance;
use App\Models\Risk;
use App\Models\RiskEvent;
use App\Models\Role;
use App\Models\RouletteBet;
use App\Models\RouletteRound;
use App\Models\Setting;
use App\Models\Staff;
use App\Models\StaffSession;
use App\Models\Table;
use App\Models\TableLimit;
use App\Models\Till;
use App\Models\User;
use App\Models\UserAuthorization;
use App\Models\UserBank;
use App\Models\UserBankAccount;
use App\Models\UserBankAccountOperation;
use App\Models\UserBonus;
use App\Models\UserSession;
use App\Models\UserStatus;
use App\Models\UserStatusChange;
use App\Models\UserStatusPoint;
use App\Models\UserTill;
use App\Models\WithdrawalRequest;

$nav = (new Page())->setTitle(null)->setPages(function (Page $g) {
    $g->add()->setUrl('/admin_panel')->setIcon('fa fa-dashboard')->setTitle('Dashboard')->setAccessLogic(function () {
        return auth()->user()->isAbleTo(['manage_everything', 'manage_dashboard']);
    });

    $g->add()->setIcon('fa fa-address-book')->setTitle('Staff')->setPages(function (Page $p) {
        $p->add(Staff::class);
        $p->add(Note::class)->setUrl('admin_panel/notes_staff')->setAccessLogic(function () {
            return auth()->user()->isAbleTo(['manage_everything', 'manage_staff_notes']);
        });
        $p->add(Role::class);
        $p->add(Permission::class);
        $p->add(StaffSession::class);
    });

    $g->add()->setIcon('fa fa-group')->setTitle('Users')->sBadge('todaysRegistrations')->setPages(function (Page $p) {
        $p->add(User::class)->sBadge('todaysRegistrations');
        $p->add(Note::class)->setUrl('admin_panel/notes_user')->setAccessLogic(function () {
            return auth()->user()->isAbleTo(['manage_everything', 'manage_user_notes']);
        });
        $p->add(UserAuthorization::class);
        $p->add(UserTill::class);
        $p->add(UserSession::class);
    });

    $g->add()->setIcon('fa fa-money')->setTitle('Finances')->sBadge('totalRequests')->setPages(function (Page $p) {
        $p->add(Till::class);
        $p->add(Currency::class);
        $p->add(Gateway::class);
//        $p->add(GatewayCurrency::class);
//        $p->add(Operation::class);
        $p->add(DepositRequest::class)->sBadge('depositRequests');
//        $p->add(DepositRequestStatusChange::class);
        $p->add(WithdrawalRequest::class)->sBadge('withdrawalRequests');
//        $p->add(WithdrawalRequestStatusChange::class);
    });

    $g->add()->setIcon('fa fa-bank')->setTitle('Banks')->setPages(function (Page $p) {
        $p->add(Bank::class);
        $p->add(BankAccount::class);
        $p->add(BankAccountOperation::class);
//        $p->add(InternalOperation::class);
        $p->add(UserBank::class);
        $p->add(UserBankAccount::class);
        $p->add(UserBankAccountOperation::class);
    });

    $g->add()->setIcon('fa fa-gamepad')->setTitle('Games')->setPages(function (Page $p) {
        $p->add(Game::class);
        $p->add(Table::class);
        $p->add(TableLimit::class);
//        $p->add(TableLimitCurrency::class);
//        $p->add(ResultLimitCurrency::class);
//        $p->add(BaccaratResult::class);
//        $p->add(RouletteResult::class);
        $p->add(Nominal::class);
//        $p->add(NominalValue::class);
    });

    $g->add()->setIcon('fa fa-handshake-o')->setTitle('Agents')->setPages(function (Page $p) {
        $p->add(Agent::class);
        $p->add(AgentLink::class);
        $p->add(AgentReward::class);
        $p->add()->setUrl('/admin_panel/partnership_settings')->setAccessLogic(function () {
            return auth()->user()->isAbleTo(['manage_everything', 'manage_partnership_settings']);
        })->setTitle('PartnershipSettings');
//        $p->add(AgentBet::class);
    });

    $g->add()->setIcon('fa fa-gift')->setTitle('Bonuses')->setPages(function (Page $p) {
        $p->add(Bonus::class);
        $p->add(UserBonus::class);
        $p->add(Holiday::class);
        $p->add(BonusReward::class);
//        $p->add(BetsBankAccrual::class);
        $p->add(BetsBonusAmount::class);
    });

    $g->add()->setIcon('fa fa-star')->setTitle('Statuses')->setPages(function (Page $p) {
        $p->add(UserStatus::class);
//        $p->add(UserStatusLimit::class);
        $p->add(UserStatusPoint::class);
        $p->add(UserStatusChange::class);
    });

    $g->add()->setIcon('fa fa-gamepad')->setTitle('Baccarat')->setPages(function (Page $p) {
        $p->add(BaccaratRound::class);
        $p->add(BaccaratBet::class);
        $p->add(BaccaratShoe::class);
        $p->add(LoopCommandEvent::class)->setUrl('admin_panel/loop_command_events_baccarat?type=baccarat_round')->setAccessLogic(function () {
            return auth()->user()->isAbleTo(['manage_everything', 'manage_baccarat_loop_command_events']);
        });
    });

    $g->add()->setIcon('fa fa-gamepad')->setTitle('Roulette')->setPages(function (Page $p) {
        $p->add(RouletteRound::class);
        $p->add(RouletteBet::class);
        $p->add(LoopCommandEvent::class)->setUrl('admin_panel/loop_command_events_roulette?type=roulette_round')->setAccessLogic(function () {
            return auth()->user()->isAbleTo(['manage_everything', 'manage_roulette_loop_command_events']);
        });
    });

    $g->add()->setIcon('fa fa-fire')->setTitle('Risks')->setPages(function (Page $p) {
        $p->add(Risk::class);
        $p->add(RiskEvent::class);
    });

    $g->add()->setIcon('fa fa-file-text-o')->setTitle('Reports')->setPages(function (Page $p) {
        $p->add(ReportsBaccaratBet::class);
        $p->add(ReportsRouletteBet::class);
        $p->add(ReportsDealerShift::class);
        $p->add(ReportsUserFinance::class);
        $p->add()->setUrl('/admin_panel/reports/reports_transactions')->setTitle('ReportsTransactions')->setAccessLogic(function () {
            return auth()->user()->isAbleTo(['manage_everything', 'manage_reports_transactions']);
        });
        $p->add()->setUrl('/admin_panel/reports/reports_transactions_analysis')->setTitle('ReportsTransactionsAnalysis')->setAccessLogic(function () {
            return auth()->user()->isAbleTo(['manage_everything', 'manage_reports_transactions_analysis']);
        });
        $p->add()->setUrl('/admin_panel/reports/reports_activity_analysis')->setTitle('ReportsActivityAnalysis')->setAccessLogic(function () {
            return auth()->user()->isAbleTo(['manage_everything', 'manage_reports_activity_analysis']);
        });
        $p->add()->setUrl('/admin_panel/reports/reports_games_statistics')->setTitle('ReportsGamesStatistics')->setAccessLogic(function () {
            return auth()->user()->isAbleTo(['manage_everything', 'manage_reports_games_statistics']);
        });
        $p->add()->setUrl('/admin_panel/reports/reports_bonuses')->setTitle(trans('ReportsBonuses'))->setAccessLogic(function () {
            return auth()->user()->isAbleTo(['manage_everything', 'manage_reports_bonuses']);
        });
        $p->add()->setUrl('/admin_panel/reports/reports_operator_shifts')->setTitle(trans('ReportsOperatorShifts'))->setAccessLogic(function () {
            return auth()->user()->isAbleTo(['manage_everything', 'manage_reports_operator_shifts']);
        });
        $p->add()->setUrl('/admin_panel/reports/reports_agents')->setTitle(trans('ReportsAgents'))->setAccessLogic(function () {
            return auth()->user()->isAbleTo(['manage_everything', 'manage_reports_agents']);
        });
        $p->add()->setUrl('/admin_panel/reports/reports_closing_statement')->setTitle('ReportsClosingStatement')->setAccessLogic(function () {
            return auth()->user()->isAbleTo(['manage_everything', 'manage_reports_closing_statement']);
        });
    });

    $g->add((Region::class))->setIcon('fa fa-map-marker');
    $g->add((Setting::class))->setIcon('fa fa-cogs');
    $g->add((ExchangeRate::class))->setIcon('fa fa-line-chart');
    $g->add((Notification::class))->setIcon('fa fa-bell');
    $g->add((McChat::class))->setIcon('fa fa-comments-o');
});

$navigation->setFromArray($nav->getPages()->all());