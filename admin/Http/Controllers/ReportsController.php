<?php

namespace Admin\Http\Controllers;

use Admin\Custom\PartnerProgram\PartnerProgram;
use App\Custom\CustomCollection;
use App\Models\Agent;
use App\Models\BaccaratBet;
use App\Models\BankAccount;
use App\Models\BankAccountOperation;
use App\Models\BaseModel;
use App\Models\Currency;
use App\Models\Operation;
use App\Models\ReportsUserFinance;
use App\Models\RouletteBet;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportsController extends BaseReportsController
{
    public function getReportsTransactions(Request $request, $dateFrom, $dateTo)
    {
        $bankAccounts = BankAccount::where('bank_accounts.status', 'active')
                                   ->join('bank_account_operations as bao', function ($j) use ($dateFrom, $dateTo) {
                                       $j->on("bank_accounts.id", '=', "bao.bank_account_id")
                                         ->whereDate('bao.created_at', '>=', $dateFrom)->whereDate('bao.created_at', '<=', $dateTo);
                                   })
                                   ->leftJoin('deposit_requests as dr', function ($j) use ($dateFrom, $dateTo) {
                                       $j->on('bao.operatable_id', '=', 'dr.id')->where('bao.operatable_type', '=', 'deposit_request');
                                   })
                                   ->leftJoin('withdrawal_requests as wr', function ($j) use ($dateFrom, $dateTo) {
                                       $j->on('bao.operatable_id', '=', 'wr.id')->where('bao.operatable_type', '=', 'withdrawal_request');
                                   })
                                   ->leftJoin('banks as b', function ($j) use ($dateFrom, $dateTo) {
                                       $j->on('b.id', '=', 'bank_accounts.bank_id');
                                   })
                                   ->selectRaw("SUM(IF(bao.operatable_type = 'deposit_request', dr.received_amount, 0)) AS `received_deposit_amount`")
                                   ->selectRaw("SUM(IF(bao.operatable_type = 'withdrawal_request', wr.received_amount, 0)) AS `received_withdrawal_amount`")
                                   ->selectRaw("SUM(IF(bao.operatable_type = 'deposit_request', dr.total_amount, 0)) AS `total_deposit_amount`")
                                   ->selectRaw("SUM(IF(bao.operatable_type = 'withdrawal_request', wr.total_amount, 0)) AS `total_withdrawal_amount`")
                                   ->selectRaw("SUM(IF(bao.operatable_type = 'internal_operations' AND bao.value > '0', bao.value, 0)) AS `io_deposit_amount`")
                                   ->selectRaw("SUM(IF(bao.operatable_type = 'internal_operations' AND bao.value < '0', bao.value, 0)) AS `io_withdrawal_amount`")
                                   ->selectRaw("CONCAT(b.slug, ' - ', bank_accounts.number)  AS `title`")
                                   ->selectRaw("bank_accounts.currency_id")->selectRaw("bank_accounts.id")->selectRaw("bank_accounts.number")
                                   ->groupBy("bank_accounts.id", 'bank_accounts.number')->get();

        foreach (count($bankAccounts) ? $bankAccounts : $bankAccounts = BankAccount::with(['bank'])->get() as $bankAccount) {
            $balance_before = BankAccountOperation::whereDate('created_at', '<', $dateFrom)->where('bank_account_id', $bankAccount->id)
                                                  ->orderBy('id', 'desc')->select('balance')->first();
            $balance_after  = BankAccountOperation::whereDate('created_at', '<=', $dateTo)->where('bank_account_id', $bankAccount->id)
                                                  ->orderBy('id', 'desc')->select('balance')->first();

            $bankAccount->balance_before = $balance_before ? $balance_before->balance : 0;
            $bankAccount->balance_after  = $balance_after ? $balance_after->balance : 0;
            $bankAccount->title          = $bankAccount->title ?? "{$bankAccount->bank->slug} - {$bankAccount->number}";
        }
        $groupedBankAccounts = $bankAccounts->groupBy("currency_id");

        return ['groupedBankAccounts' => $groupedBankAccounts];
    }

    public function getReportsTransactionsAnalysis(Request $request, $dateFrom, $dateTo)
    {
        $requests = new CustomCollection();
        foreach (['DepositRequest', 'WithdrawalRequest'] as $req) {
            $query       = app()->make("App\\Models\\{$req}")->whereStatus('succeed')
                                ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<=', $dateTo);
            $obj         = new \stdClass();
            $obj->type   = $req;
            $obj->count  = $query->count();
            $obj->amount = $query->sum('default_amount');
            $obj->min    = $query->min('default_amount');
            $obj->max    = $query->max('default_amount');
            $obj->avg    = round($query->avg('default_amount'), 2);
            $requests->push($obj);
        }

        return ['requests' => $requests];
    }

    public function getReportsActivityAnalysis(Request $request, $dateFrom, $dateTo)
    {
        $baccaratBetTypes = config('selectOptions.baccarat_results.code');
        $baccaratBets     = BaccaratBet::from('baccarat_bets as bb')->whereIn('bb.status', ['won', 'lost'])
                                       ->whereDate('bb.created_at', '>=', $dateFrom)->whereDate('bb.created_at', '<=', $dateTo)
                                       ->leftJoin('baccarat_results as br', function ($j) use ($dateFrom, $dateTo) {
                                           $j->on('bb.baccarat_result_id', '=', 'br.id');
                                       });
        foreach ($baccaratBetTypes as $baccaratBetType) {
            $baccaratBets = $baccaratBets->selectRaw("SUM(IF(br.code = '{$baccaratBetType}', bb.default_amount, 0)) AS `{$baccaratBetType}`");
        }
        $baccaratBets = $baccaratBets->selectRaw("SUM(bb.default_amount) AS `total_amount`");
        $baccaratBets = $baccaratBets->selectRaw("bb.status")->groupBy("bb.status")->get();

        $rouletteBetTypes = config('selectOptions.roulette_results.code');
        $rouletteBets     = RouletteBet::from('roulette_bets as rb')->whereIn('rb.status', ['won', 'lost'])
                                       ->whereDate('rb.created_at', '>=', $dateFrom)->whereDate('rb.created_at', '<=', $dateTo)
                                       ->leftJoin('roulette_result_presets as rrp', function ($j) use ($dateFrom, $dateTo) {
                                           $j->on('rb.roulette_result_preset_id', '=', 'rrp.id');
                                       })
                                       ->leftJoin('roulette_results as rr', function ($j) use ($dateFrom, $dateTo) {
                                           $j->on('rrp.roulette_result_id', '=', 'rr.id');
                                       });
        foreach ($rouletteBetTypes as $rouletteBetType) {
            if (in_array($rouletteBetType, ['column1', 'column2', 'column3', 'dozen1', 'dozen2', 'dozen3'])) {
                $index        = str_replace(['column', 'dozen'], '', $rouletteBetType) - 1;
                $type         = strpos($rouletteBetType, 'column') === 0 ? 'column' : 'dozen';
                $rouletteBets = $rouletteBets->selectRaw("SUM(IF(rr.code = '{$type}' AND rrp.index = {$index}, rb.default_amount, 0)) AS `{$rouletteBetType}`");
            } else {
                $rouletteBets = $rouletteBets->selectRaw("SUM(IF(rr.code = '{$rouletteBetType}', rb.default_amount, 0)) AS `{$rouletteBetType}`");
            }
        }
        for ($i = 0; $i <= 36; $i++) {
            $rouletteBets = $rouletteBets->selectRaw("SUM(IF(rr.code = 'straight' AND rrp.index = {$i}, rb.default_amount, 0)) AS `n{$i}`");
        }
        $rouletteBets = $rouletteBets->selectRaw("SUM(rb.default_amount) AS `total_amount`");
        $rouletteBets = $rouletteBets->selectRaw("rb.status")->groupBy("rb.status")->get();

        return [
            'baccaratBets'     => $baccaratBets,
            'baccaratBetTypes' => $baccaratBetTypes,
            'rouletteBetTypes' => $rouletteBetTypes,
            'rouletteBets'     => $rouletteBets
        ];
    }

    public function getReportsGamesStatistics(Request $request, $dateFrom, $dateTo)
    {
        $currencyId = $request->get('currency_id') ? $request->get('currency_id') : null;
        $valueFrom  = $request->get('value_from') ? $request->get('value_from') : 0;
        $valueTo    = $request->get('value_to') ? $request->get('value_to') : 99999999999;

        $groupedData =
            Operation::from('operations as op')->whereDate('op.created_at', '>=', $dateFrom)->whereDate('op.created_at', '<', $dateTo)
                     ->join('user_tills as ut', function ($j) use ($dateFrom, $dateTo, $currencyId) {
                         $j->on("ut.id", '=', "op.user_till_id");
                         if ($currencyId) {
                             $j->where('ut.currency_id', '=', $currencyId);
                         }
                     })
                     ->leftJoin('baccarat_bets as bb', function ($j) use ($dateFrom, $dateTo, $currencyId, $valueFrom, $valueTo) {
                         $filteredColumn = $currencyId ? 'amount' : 'default_amount';

                         $j->on('op.operatable_id', '=', 'bb.id')->where('op.operatable_type', '=', 'baccarat_bet')
                           ->where("bb.{$filteredColumn}", '>=', $valueFrom)->where("bb.{$filteredColumn}", '<=', $valueTo);
                     })
                     ->leftJoin('roulette_bets as rb', function ($j) use ($dateFrom, $dateTo, $currencyId, $valueFrom, $valueTo) {
                         $filteredColumn = $currencyId ? 'amount' : 'default_amount';

                         $j->on('op.operatable_id', '=', 'rb.id')->where('op.operatable_type', '=', 'roulette_bet')
                           ->where("rb.{$filteredColumn}", '>=', $valueFrom)->where("rb.{$filteredColumn}", '<=', $valueTo);
                     })
                     ->selectRaw("(COUNT(IF(op.operatable_type = 'baccarat_bet' AND op.amount < 0, TRUE, NULL))) AS `baccarat_bets_count`")
                     ->selectRaw("(COUNT(IF(op.operatable_type = 'roulette_bet' AND op.amount < 0, TRUE, NULL))) AS `roulette_bets_count`")
                     ->selectRaw("(SUM(IF(op.operatable_type = 'baccarat_bet' AND op.amount < 0, bb.default_amount, 0))) AS `baccarat_bets_amount`")
                     ->selectRaw("(SUM(IF(op.operatable_type = 'roulette_bet' AND op.amount < 0, rb.default_amount, 0))) AS `roulette_bets_amount`")
                     ->selectRaw("(MIN(IF(op.operatable_type = 'baccarat_bet' AND op.amount < 0, bb.default_amount, NULL))) AS `baccarat_bets_min`")
                     ->selectRaw("(MIN(IF(op.operatable_type = 'roulette_bet' AND op.amount < 0, rb.default_amount, NULL))) AS `roulette_bets_min`")
                     ->selectRaw("(MAX(IF(op.operatable_type = 'baccarat_bet' AND op.amount < 0, bb.default_amount, NULL))) AS `baccarat_bets_max`")
                     ->selectRaw("(MAX(IF(op.operatable_type = 'roulette_bet' AND op.amount < 0, rb.default_amount, NULL))) AS `roulette_bets_max`")
                     ->selectRaw("(AVG(IF(op.operatable_type = 'baccarat_bet' AND op.amount < 0, bb.default_amount, NULL))) AS `baccarat_bets_avg`")
                     ->selectRaw("(AVG(IF(op.operatable_type = 'roulette_bet' AND op.amount < 0, rb.default_amount, NULL))) AS `roulette_bets_avg`")
                     ->selectRaw("(SUM(IF(op.operatable_type = 'baccarat_bet' AND op.amount < 0, bb.default_outcome, 0))) AS `baccarat_bets_outcome`")
                     ->selectRaw("(SUM(IF(op.operatable_type = 'roulette_bet' AND op.amount < 0, rb.default_outcome, 0))) AS `roulette_bets_outcome`")
                     ->selectRaw("(SUM(IF(op.operatable_type = 'baccarat_bet' AND op.amount < 0, bb.default_amount - bb.default_outcome, 0))) AS `baccarat_bets_result`")
                     ->selectRaw("(SUM(IF(op.operatable_type = 'roulette_bet' AND op.amount < 0, rb.default_amount - rb.default_outcome, 0))) AS `roulette_bets_result`")
                     ->first();

        if ( ! $groupedData) {
            $groupedData = new Operation();
            foreach (['count', 'amount', 'outcome', 'result', 'min', 'max', 'avg'] as $groupedType) {
                $groupedData->{"baccarat_bets_{$groupedType}"} = 0;
                $groupedData->{"roulette_bets{$groupedType}"}  = 0;
            }
        }

        return ['groupedData' => $groupedData];
    }

    public function getReportsBonuses(Request $request, $dateFrom, $dateTo)
    {
        $user = request()->get('user_id') ? User::findOrFail(request()->get('user_id')) : null;

        $amountColumn = $user ? 'operations.amount' : 'user_bonuses.default_amount';

        $qb = \DB::table('bonuses')
                 ->selectRaw('bonuses.id, bonuses.name, bonus_translations.title, COUNT(operations.id) AS count, SUM(' . $amountColumn . ') AS sum')
                 ->join('bonus_translations', function ($join) {
                     $join->on('bonus_translations.bonus_id', '=', 'bonuses.id');
                     $join->where('bonus_translations.locale', session()->get('admin.locale'));
                 })
                 ->leftJoin('user_bonuses', function ($join) use ($request) {
                     $join->on('bonuses.id', '=', 'user_bonuses.bonus_id');
                 })
                 ->leftJoin('operations', function ($join) use ($user, $dateFrom, $dateTo) {
                     $join->on('operations.operatable_id', '=', 'user_bonuses.id');

                     $join->join('user_tills', function ($join) use ($user) {
                         $join->on('operations.user_till_id', '=', 'user_tills.id');

                         if ($user) {
                             $join->where('user_tills.id', '=', $user->moneyTill->id);
                         } else {
                             $join->where('user_tills.till_id', '=', 1);
                         }
                     });

                     $join->where('operations.operatable_type', 'user_bonus');

                     if ($dateFrom) {
                         $join->where('operations.created_at', '>=', $dateFrom);
                     }

                     if ($dateTo) {
                         $join->where('operations.created_at', '<=', $dateTo);
                     }
                 })
                 ->groupBy('bonuses.id', 'bonus_translations.title');

        $bonuses = $qb->get();

        return ['bonuses' => $bonuses, 'user' => $user];
    }

    public function getReportsOperatorShifts(Request $request, $dateFrom, $dateTo)
    {
        $staffId      = $request->get('staffId') ?? auth()->id();
        $bankAccounts =
            BankAccountOperation::from('bank_account_operations as bao')
                                ->whereDate('bao.created_at', '>=', $dateFrom)->whereDate('bao.created_at', '<', $dateTo)
                                ->join('bank_accounts as ba', function ($j) use ($dateFrom, $dateTo) {
                                    $j->on('ba.id', '=', 'bao.bank_account_id');
                                })
                                ->leftJoin('deposit_requests as dr', function ($j) use ($dateFrom, $dateTo) {
                                    $j->on('bao.operatable_id', '=', 'dr.id')->where('bao.operatable_type', '=', 'deposit_request');
                                })
                                ->leftJoin('deposit_request_status_changes as drsc', function ($j) use ($dateFrom, $dateTo, $staffId) {
                                    $j->on('drsc.deposit_request_id', '=', 'dr.id')->where('drsc.staff_id', '=', $staffId);
                                })
                                ->leftJoin('withdrawal_requests as wr', function ($j) use ($dateFrom, $dateTo) {
                                    $j->on('bao.operatable_id', '=', 'wr.id')->where('bao.operatable_type', '=', 'withdrawal_request');
                                })
                                ->leftJoin('withdrawal_request_status_changes as wrsc', function ($j) use ($dateFrom, $dateTo, $staffId) {
                                    $j->on('wrsc.withdrawal_request_id', '=', 'wr.id')->where('wrsc.staff_id', '=', $staffId);
                                })
                                ->selectRaw("SUM((IF(drsc.status = 'approved_to_proceed' AND (drsc.options IS NULL OR drsc.options->>'$.old' <> 'true'), dr.total_amount, 0))) AS `deposits_amount`")
                                ->selectRaw("SUM((IF(wrsc.status = 'approved_to_proceed' AND (wrsc.options IS NULL OR wrsc.options->>'$.old' <> 'true'), wr.total_amount, 0))) AS `withdrawals_amount`")
                                ->selectRaw("ba.currency_id")->selectRaw("ba.id")->selectRaw("ba.number")
                                ->groupBy("ba.id", "ba.number")->get();

        foreach (count($bankAccounts) ? $bankAccounts : $bankAccounts = BankAccount::active()->get() as $bankAccount) {
            $bankAccount->balance_alter = $bankAccount->deposits_amount - $bankAccount->withdrawals_amount;
        }
        $groupedBankAccounts = $bankAccounts->groupBy("currency_id");

        return ['groupedBankAccounts' => $groupedBankAccounts];
    }

    public function getReportsAgents(Request $request)
    {
        if ($userId = $request->get('user_id')) {
            $agent = Agent::with('ancestors', 'descendants', 'children', 'user')
                          ->withDepth()
                          ->where('user_id', $userId)
                          ->first();

            $agentStatistics = [];

            if ($agent) {
                $period = $request->get('date_from') && $request->get('date_to')
                    ? [Carbon::parse($request->get('date_from'))->setTime(0, 0), Carbon::parse($request->get('date_to'))->setTime(23, 59, 59)]
                    : null;

                $ownBetsBank = PartnerProgram::calculateOwnBetsBank($agent->user_id, $period);

                $betsBank     = PartnerProgram::calculateBetsBank($agent->user_id, $period, [1]);
                $rewardAmount = PartnerProgram::calculateRewardAmount($agent->user_id, $period, [1]);

                $subAgentsBetsBank     = PartnerProgram::calculateBetsBank($agent->user_id, $period, range(2, PartnerProgram::getNetworkDepth()));
                $subAgentsRewardAmount = PartnerProgram::calculateRewardAmount($agent->user_id, $period, range(2, PartnerProgram::getNetworkDepth()));

                $agentsCount    = count($agent->children);
                $subAgentsCount = count($agent->descendants) - count($agent->children);

                $agentStatistics = [
                    'agentsCount'             => $agentsCount,
                    'ownBetsBank'             => BaseModel::formatCurrency(1, $ownBetsBank),
                    'betsBank'                => BaseModel::formatCurrency(1, $betsBank),
                    'firstLevelRewardPercent' => PartnerProgram::getLevelPercentage(1),
                    'rewardAmount'            => BaseModel::formatCurrency(1, $rewardAmount),
                    'subAgentsBetsBank'       => BaseModel::formatCurrency(1, $subAgentsBetsBank),
                    'subAgentRewardPercent'   => PartnerProgram::getLevelPercentage(2),
                    'subAgentsRewardAmount'   => BaseModel::formatCurrency(1, $subAgentsRewardAmount),
                    'subAgentsCount'          => $subAgentsCount
                ];
            }
        } else {
            $levelStatistics = PartnerProgram::getRewardsByDepth();
        }

        return [
            'pageHeader'      => trans('admin/reports_agents.pageHeader'),
            'agent'           => $agent ?? null,
            'agentStatistics' => $agentStatistics ?? null,
            'levelStatistics' => $levelStatistics ?? null
        ];
    }

    public function getReportsClosingStatement(Request $request, $dateFrom, $dateTo)
    {
        $currencies   = Currency::orderBy('id')->pluck('code', 'id')->all();
        $resultBefore = ReportsUserFinance::from('reports_user_finances as ruf')
                                          ->whereDate('created_at', '>=', $dateFrom)->whereDate('created_at', '<', $dateFrom->copy()->addDays(1));
        foreach ($currencies as $id => $currency) {
            $resultBefore
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.balance_before, 0)) AS `{$currency}_balance_before`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.bonuses_balance_before, 0)) AS `{$currency}_bonuses_balance_before`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.partners_balance_before, 0)) AS `{$currency}_partners_balance_before`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.deposits_amount_before, 0)) AS `{$currency}_deposits_amount_before`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.withdrawals_amount_before, 0)) AS `{$currency}_withdrawals_amount_before`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.default_balance_before, 0)) AS `{$currency}_default_balance_before`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.bonuses_default_balance_before, 0)) AS `{$currency}_bonuses_default_balance_before`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.partners_default_balance_before, 0)) AS `{$currency}_partners_default_balance_before`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.deposits_default_amount_before, 0)) AS `{$currency}_deposits_default_amount_before`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.withdrawals_default_amount_before, 0)) AS `{$currency}_withdrawals_default_amount_before`");
        }
        $resultBefore = $resultBefore->first();

        $resultAfter = ReportsUserFinance::from('reports_user_finances as ruf')
                                         ->whereDate('created_at', '>=', $dateTo->copy()->startOfDay())->whereDate('created_at', '<=', $dateTo);
        foreach ($currencies as $id => $currency) {
            $resultAfter
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.balance_after, 0)) AS `{$currency}_balance_after`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.bonuses_balance_after, 0)) AS `{$currency}_bonuses_balance_after`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.partners_balance_after, 0)) AS `{$currency}_partners_balance_after`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.default_balance_after, 0)) AS `{$currency}_default_balance_after`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.bonuses_default_balance_after, 0)) AS `{$currency}_bonuses_default_balance_after`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.partners_default_balance_after, 0)) AS `{$currency}_partners_default_balance_after`");
        }
        $resultAfter = $resultAfter->first();

        $groupedData =
            ReportsUserFinance::from('reports_user_finances as ruf')->whereDate('ruf.created_at', '>=', $dateFrom)->whereDate('ruf.created_at', '<=', $dateTo)
                              ->selectRaw("COUNT(ruf.balance_alteration) AS `active_users`");
        foreach ($currencies as $id => $currency) {
            $groupedData
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.deposits_amount, 0)) AS `{$currency}_deposits_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.withdrawals_amount, 0)) AS `{$currency}_withdrawals_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.baccarat_bets_amount, 0)) AS `{$currency}_baccarat_bets_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.baccarat_bets_outcome, 0)) AS `{$currency}_baccarat_bets_outcome`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.roulette_bets_amount, 0)) AS `{$currency}_roulette_bets_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.roulette_bets_outcome, 0)) AS `{$currency}_roulette_bets_outcome`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.bonuses_amount, 0)) AS `{$currency}_bonuses_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.used_bonuses_amount, 0)) AS `{$currency}_used_bonuses_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.canceled_bonuses_amount, 0)) AS `{$currency}_canceled_bonuses_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.partners_amount, 0)) AS `{$currency}_partners_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.used_partners_amount, 0)) AS `{$currency}_used_partners_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.canceled_partners_amount, 0)) AS `{$currency}_canceled_partners_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.deposits_default_amount, 0)) AS `{$currency}_deposits_default_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.withdrawals_default_amount, 0)) AS `{$currency}_withdrawals_default_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.baccarat_bets_default_amount, 0)) AS `{$currency}_baccarat_bets_default_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.baccarat_bets_default_outcome, 0)) AS `{$currency}_baccarat_bets_default_outcome`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.roulette_bets_default_amount, 0)) AS `{$currency}_roulette_bets_default_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.roulette_bets_default_outcome, 0)) AS `{$currency}_roulette_bets_default_outcome`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.bonuses_default_amount, 0)) AS `{$currency}_bonuses_default_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.used_bonuses_default_amount, 0)) AS `{$currency}_used_bonuses_default_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.canceled_bonuses_default_amount, 0)) AS `{$currency}_canceled_bonuses_default_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.partners_default_amount, 0)) AS `{$currency}_partners_default_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.used_partners_default_amount, 0)) AS `{$currency}_used_partners_default_amount`")
                ->selectRaw("SUM(IF(ruf.currency_id = {$id}, ruf.canceled_partners_default_amount, 0)) AS `{$currency}_canceled_partners_default_amount`");
        }
        $groupedData = $groupedData->first();

        $operational_start_date = Carbon::parse(Setting::where('key', 'reports_operational_start_date')->first()->value);
        $operational_start_date = Carbon::today() < $operational_start_date ? Carbon::today() : $operational_start_date;
        $days_from              = ($operational_start_date->diffInDays($dateFrom, false) + 1) >= 0 ?
            $operational_start_date->diffInDays($dateFrom, false) + 1 : trans("admin/common.WrongPeriod");
        $days_to                = $operational_start_date->diffInDays($dateTo, false) + 1;

        $arr               = [];
        $arr['daysPeriod'] = "{$days_from} - {$days_to}";
        foreach ([$resultBefore, $resultAfter, $groupedData] as $subj) {
            foreach ($subj->getAttributes() as $attrName => $attrVal) {
                if ( ! in_array($attrName, ['active_users'])) {
                    [$currencyCode, $attrName] = explode('_', $attrName, 2);
                    if (strpos($attrName, 'default_') !== false) {
                        $attrType = 'default';
                        $attrName = str_replace('default_', '', $attrName);
                    } else {
                        $attrType = 'nominal';
                    }

                    $arr[$attrName][$attrType][$currencyCode] = round($attrVal, 2);
                    $arr[$attrName][$attrType]['total']       = ($arr[$attrName][$attrType]['total'] ?? 0) + round($attrVal, 2);
                    if ($attrType === 'nominal') {
                        $attrType     = 'converted';
                        $convertedVal = round(BaseModel::convertToDefaultCurrency(array_search($currencyCode, $currencies), $attrVal), 2);

                        $arr[$attrName][$attrType][$currencyCode] = $convertedVal;
                        $arr[$attrName][$attrType]['total']       = ($arr[$attrName][$attrType]['total'] ?? 0) + $convertedVal;
                    }
                } else {
                    $arr[$attrName] = $attrVal;
                }
            }
        }

        $attrTypes = ['nominal', 'default', 'converted'];
        $addArr    = [];
        foreach ($attrTypes as $attrType) {
            foreach ($currencies as $id => $currencyCode) {
                $addArr['deposits_withdrawals_amount_before'][$attrType][$currencyCode] =
                    ($arr['deposits_amount_before'][$attrType][$currencyCode] - $arr['withdrawals_amount_before'][$attrType][$currencyCode]) ?? 0;

                $addArr['total_deposits_withdrawals_amount'][$attrType][$currencyCode] =
                    $arr['deposits_amount'][$attrType][$currencyCode] - $arr['withdrawals_amount'][$attrType][$currencyCode];

                $addArr['deposits_amount_after'][$attrType][$currencyCode] =
                    ($addArr['deposits_withdrawals_amount_before'][$attrType][$currencyCode] + $addArr['total_deposits_withdrawals_amount'][$attrType][$currencyCode]) ?? 0;

                $addArr['total_bonuses_balance_after'][$attrType][$currencyCode] =
                    ($arr['bonuses_balance_before'][$attrType][$currencyCode] + $arr['bonuses_amount'][$attrType][$currencyCode] -
                    $arr['used_bonuses_amount'][$attrType][$currencyCode] - $arr['canceled_bonuses_amount'][$attrType][$currencyCode]);

                $addArr['total_partners_balance_after'][$attrType][$currencyCode] =
                    ($arr['partners_balance_before'][$attrType][$currencyCode] + $arr['partners_amount'][$attrType][$currencyCode] -
                    $arr['used_partners_amount'][$attrType][$currencyCode] - $arr['canceled_partners_amount'][$attrType][$currencyCode]);

                $addArr['baccarat_bets_result'][$attrType][$currencyCode] =
                    $arr['baccarat_bets_amount'][$attrType][$currencyCode] - $arr['baccarat_bets_outcome'][$attrType][$currencyCode];

                $addArr['roulette_bets_result'][$attrType][$currencyCode] =
                    $arr['roulette_bets_amount'][$attrType][$currencyCode] - $arr['roulette_bets_outcome'][$attrType][$currencyCode];

                $addArr['total_bets_amount'][$attrType][$currencyCode] =
                    -($addArr['baccarat_bets_result'][$attrType][$currencyCode] + $addArr['roulette_bets_result'][$attrType][$currencyCode]);

                $addArr['total_balance_after'][$attrType][$currencyCode] =
                    ($arr['balance_before'][$attrType][$currencyCode] + $addArr['total_deposits_withdrawals_amount'][$attrType][$currencyCode]) +
                    ($arr['used_bonuses_amount'][$attrType][$currencyCode] + $arr['used_partners_amount'][$attrType][$currencyCode] + $addArr['total_bets_amount'][$attrType][$currencyCode]);
            }
        }
        foreach ($addArr as $elemName => $elem) {
            foreach ($elem as $attrTypeName => $attrType) {
                $addArr[$elemName][$attrTypeName]['total'] = array_sum($attrType);
            }
        }
        $obj = BaseModel::arrayToObject($arr + $addArr);

        $bankAccounts = BankAccount::where('bank_accounts.status', 'active')
                                   ->join('bank_account_operations as bao', function ($j) use ($dateFrom, $dateTo) {
                                       $j->on("bank_accounts.id", '=', "bao.bank_account_id")
                                         ->whereDate('bao.created_at', '>=', $dateFrom)->whereDate('bao.created_at', '<=', $dateTo);
                                   })
                                   ->leftJoin('deposit_requests as dr', function ($j) use ($dateFrom, $dateTo) {
                                       $j->on('bao.operatable_id', '=', 'dr.id')->where('bao.operatable_type', '=', 'deposit_request');
                                   })
                                   ->leftJoin('withdrawal_requests as wr', function ($j) use ($dateFrom, $dateTo) {
                                       $j->on('bao.operatable_id', '=', 'wr.id')->where('bao.operatable_type', '=', 'withdrawal_request');
                                   })
                                   ->leftJoin('banks as b', function ($j) use ($dateFrom, $dateTo) {
                                       $j->on('b.id', '=', 'bank_accounts.bank_id');
                                   })
                                   ->selectRaw("SUM(IF(bao.operatable_type = 'deposit_request', dr.total_amount, 0)) AS `total_deposit_amount`")
                                   ->selectRaw("SUM(IF(bao.operatable_type = 'deposit_request', dr.default_amount, 0)) AS `default_total_deposit_amount`")
                                   ->selectRaw("SUM(IF(bao.operatable_type = 'withdrawal_request', wr.total_amount, 0)) AS `total_withdrawal_amount`")
                                   ->selectRaw("SUM(IF(bao.operatable_type = 'withdrawal_request', wr.default_amount, 0)) AS `default_total_withdrawal_amount`")
                                   ->selectRaw("SUM(IF(bao.operatable_type = 'internal_operations' AND bao.value > '0', bao.value, 0)) AS `io_deposit_amount`")
                                   ->selectRaw("SUM(IF(bao.operatable_type = 'internal_operations' AND bao.value > '0', bao.default_value, 0)) AS `default_io_deposit_amount`")
                                   ->selectRaw("SUM(IF(bao.operatable_type = 'internal_operations' AND bao.value < '0', bao.value, 0)) AS `io_withdrawal_amount`")
                                   ->selectRaw("SUM(IF(bao.operatable_type = 'internal_operations' AND bao.value < '0', bao.default_value, 0)) AS `default_io_withdrawal_amount`")
                                   ->selectRaw("CONCAT(b.slug, ' - ', bank_accounts.number)  AS `title`")
                                   ->selectRaw("bank_accounts.currency_id")->selectRaw("bank_accounts.id")->selectRaw("bank_accounts.number")
                                   ->groupBy("bank_accounts.id", 'bank_accounts.number')->get();

        foreach ($allBankAccounts = BankAccount::active()->get() as $bA) {
            if ( ! $bankAccounts->where('id', $bA->id)->first()) {
                $bankAccounts->push($bA);
            }
        }

        foreach ($bankAccounts as $bankAccount) {
            $operationBefore = BankAccountOperation::whereDate('created_at', '<', $dateFrom)->where('bank_account_id', $bankAccount->id)
                                                   ->orderBy('id', 'desc')->select('balance', 'default_balance')->first();
            $operationAfter  = BankAccountOperation::whereDate('created_at', '<=', $dateTo)->where('bank_account_id', $bankAccount->id)
                                                   ->orderBy('id', 'desc')->select('balance', 'default_balance')->first();

            $bankAccount->title                        = $bankAccount->title ?? "{$bankAccount->bank->slug} - {$bankAccount->number}";
            $bankAccount->balance_before               = $operationBefore ? $operationBefore->balance : 0;
            $bankAccount->default_balance_before       = $operationBefore ? $operationBefore->default_balance : 0;
            $bankAccount->balance_after                = $operationAfter ? $operationAfter->balance : 0;
            $bankAccount->default_balance_after        = $operationAfter ? $operationAfter->default_balance : 0;
            $bankAccount->default_deposit_amount       = $bankAccount->default_total_deposit_amount ?? 0;
            $bankAccount->default_withdrawal_amount    = $bankAccount->default_total_withdrawal_amount ?? 0;
            $bankAccount->default_io_deposit_amount    = $bankAccount->default_io_deposit_amount ?? 0;
            $bankAccount->default_io_withdrawal_amount = $bankAccount->default_io_withdrawal_amount ?? 0;
        }
        $groupedBankAccounts = $bankAccounts->groupBy("currency_id");

        return [
            'obj'                 => $obj,
            'currencies'          => $currencies,
            'attrTypes'           => $attrTypes,
            'groupedBankAccounts' => $groupedBankAccounts,
            'allBankAccounts'     => $bankAccounts
        ];
    }
}