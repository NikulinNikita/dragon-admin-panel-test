<?php

namespace Admin\Custom\PartnerProgram;

use App\Models\Agent;
use App\Models\AgentRewardLimit;
use App\Models\BetsBankAccrual;
use App\Models\Setting;
use App\Models\User;
use Admin\Custom\PartnerProgram\BetsBankCalculator\BetsBankCalculatorBaccarat;
use Admin\Custom\PartnerProgram\BetsBankCalculator\BetsBankCalculatorRoulette;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PartnerProgram
{
    protected static $networkDepth;
    protected static $rewardDays;
    protected static $rewardHour;
    protected static $levelPercentage;

    protected static $betsBankCalculators = [
        'baccarat' => BetsBankCalculatorBaccarat::class,
        'roulette' => BetsBankCalculatorRoulette::class
    ];

    private static $argumentsHash;
    private static $data;


    public static function calculateRewardAmount($userId, array $period = null, array $depths = []): float
    {
        $data = self::getData($userId, $depths, $period);

        return isset($data[$userId])
            ? $data[$userId]['rewardAmount']
            : 0;
    }

    public static function calculateBetsBank($userId, array $period = null, array $depths = []): float
    {
        $data = self::getData($userId, $depths, $period);

        return isset($data[$userId])
            ? $data[$userId]['betsBankAmount']
            : 0;
    }

    public static function calculateOwnBetsBank($userId, array $period = null)
    {
        if (!$period) {
            $period = self::getCurrentPeriod();
        }

        $totalBetBankAmount = 0;
        foreach (self::$betsBankCalculators as $calculatorClass) {
            $calculatorClass::calculate($userId, function($round, $roundStats) use (&$totalBetBankAmount) {
                $totalBetBankAmount+= $roundStats->bets_bank_total_default_amount;
            }, $period);
        }

        return $totalBetBankAmount;
    }

    public static function getData($userId, array $depths = null, array $period = null)
    {
        $argsHash = md5(var_export([$userId, $period], true));

        if ($argsHash != self::$argumentsHash) {
            self::$argumentsHash = $argsHash;
            self::$data = self::collectData($userId, $period);
        }

        return self::filterDataByDepths($depths);
    }

    protected static function collectData($userId, array $period = null)
    {
        $data = [];

        foreach (self::getSubAgents($userId) as $subAgent) {
            $topLevelAgents = Agent::withDepth()
                ->whereAncestorOf($subAgent)
                ->having('depth', '<=', $subAgent->depth)
                ->orderBy('depth', 'desc')
                ->take(self::getNetworkDepth())
                ->get();

            foreach (self::$betsBankCalculators as $calculatorClass) {
                $calculatorClass::calculate($subAgent->user_id, function($round, $roundStats) use (&$data, $subAgent, $topLevelAgents) {
                    foreach ($topLevelAgents as $topLevelAgent) {
                        if ($topLevelAgent->status != 'active') {
                            continue;
                        }

                        self::processRound($data[$topLevelAgent->user_id], $round, $roundStats, $subAgent, $topLevelAgent);
                    }
                }, $period);
            }
        }

        return $data;
    }

    protected static function processRound(&$dataItem, $round, $roundStats, Agent $agent, Agent $topLevelAgent)
    {
        if (!isset($dataItem)) {
            $dataItem = [
                'betsBankAmount' => 0,
                'rewardAmount' => 0,
                'rounds' => []
            ];
        }

        if (/*$roundBets->isEmpty() || */$agent->status != 'active') {
            return;
        }

        if (!$roundStats) {
            throw new \Exception(sprintf('No round stats (%s, #%d)', $round->getTable(), $round->id));
        }

        $percent = self::getRewardPercent($agent, $topLevelAgent);
        $rewardAmount = $roundStats->bets_bank_total_default_amount / 100 * $percent;

        $dataItem['rounds'][] = [
            'agent_id' => $topLevelAgent->user_id,
            'player_id' => $agent->user_id,
            'distance' => $agent->depth - $topLevelAgent->depth,
            'statistics' => [
                'betsAmount' => $roundStats->bets_total_default_amount,
                'payoutsAmount' => $roundStats->bets_total_default_outcome,
                'betsBankAmount' => $roundStats->bets_bank_total_default_amount,
                'levelPercent' => $percent,
                'rewardAmount' => $rewardAmount
            ]
        ];

        if ($rewardAmount) {
            $dataItem['betsBankAmount']+= $roundStats->bets_bank_total_default_amount;
            $dataItem['rewardAmount']+= $rewardAmount;
        }
    }

    private static function filterDataByDepths($depths)
    {
        $depths = $depths ?? range(1, self::getNetworkDepth());

        $data = self::$data;

        foreach ($data as $userId => &$item) {
            foreach ($item['rounds'] as $i => &$round) {
                //$levelDistance = $round['playerAgent']['depth'] - $round['agent']['depth'];

                //if (!in_array($levelDistance, $depths)) {
                if (!in_array($round['distance'], $depths)) {
                    $item['betsBankAmount']-= $round['statistics']['betsBankAmount'];
                    $item['rewardAmount']-= $round['statistics']['rewardAmount'];

                    unset($item['rounds'][$i]);
                }
            }
        }

        return $data;
    }

    // custom method to fetch rounds between two agents
    // referenced in AgentController::reportsByRoundPage()
    public static function getRounds(Agent $agent, Agent $player): array
    {
        $rounds = [];

        $percent = self::getRewardPercent($player, $agent);

        foreach (self::$betsBankCalculators as $calculatorClass) {
            $calculatorClass::calculate($player->user_id, function($round, $roundStats) use (&$rounds, $agent, $player, $percent) {
                $rounds[] = [
                    'round' => array_merge($round->toArray(), [
                        'table' => $round->staffSession->table_id,
                        'game' => $round->staffSession->table->game->slug
                    ]),
                    'statistics' => array_merge($roundStats->toArray(), [
                        'rewardAmount' => $roundStats->bets_bank_total_default_amount / 100 * $percent
                    ])
                ];
            });
        }

        return $rounds;
    }



    public static function getRewardPercent(Agent $player, Agent $agent): float
    {
        return self::getLevelPercentage($player->depth - $agent->depth) ?? 0;
    }

    public static function fetchLimit($type, $currencyId)
    {
        return ($limit = AgentRewardLimit::where(['type' => $type, 'currency_id' => $currencyId])->first())
            ? $limit->value
            : null;
    }

    // possibly split to 2 parts
    public static function getLevelPercentage($level)
    {
        if ($level > self::getNetworkDepth()) {
            return false;
        }

        $level--;

        self::getCachedSetting(self::$levelPercentage, 'partnership_level_percent');

        if (is_string(self::$levelPercentage)) {
            self::$levelPercentage = json_decode(self::$levelPercentage);
        }

        if (isset(self::$levelPercentage[$level])) {
            return self::$levelPercentage[$level];
        }

        return null;
    }

    public static function getCurrentPeriod(): array
    {
        return [self::getPreviousRewardsTransferDate(), self::getNextRewardsTransferDate()];
    }

    public static function getPreviousRewardsTransferDate(): Carbon
    {
        $days = self::getRewardDays();
        rsort($days);

        $currentDay = Carbon::now()->day;

        foreach ($days as $day) {
            if ($day < 1 || $day > 31) continue;
            if ($day < $currentDay) {
                return Carbon::now()->day($day)->setTime(0, 0, 0);
            }
        }

        return Carbon::now()->subMonth(1)->day($days[0]);
    }

    public static function getNextRewardsTransferDate(): Carbon
    {
        $days = self::getRewardDays();
        sort($days);

        $currentDay = Carbon::now()->day;
        $runningTime = self::getRewardTime();

        $result = Carbon::now()->setTime($runningTime->hour, $runningTime->minute);

        foreach ($days as $day) {
            if ($day < 1 || $day > 31) continue;
            if ($day > $currentDay) {
                return $result->day($day);
            }
        }

        return $result->addMonth(1)->day($days[0]);
    }

    public static function getRewardTime(): Carbon
    {
       $runHour = self::getCachedSetting(self::$rewardHour, 'partnership_reward_run_hour');
       $runHour = explode(':', $runHour);

       return Carbon::now()->setTime($runHour[0], $runHour[1]);
    }

    public static function getNetworkDepth(): int
    {
        return (int)self::getCachedSetting(self::$networkDepth, 'partnership_network_depth');
    }

    public static function getRewardDays(): array
    {
        self::getCachedSetting(self::$rewardDays, 'partnership_reward_run_days');

        if (!is_array(self::$rewardDays)) {
            self::$rewardDays = explode(',', self::$rewardDays);
        }

        return self::$rewardDays;
    }

    protected static function getCachedSetting(&$variable, $settingName)
    {
        if (is_null($variable)) {
            $variable = Setting::where('key', $settingName)
                ->first()
                ->value;
        }

        return $variable;
    }

    public static function getSubAgentsIds($agentId, array $depths = null, $includeSelf = false, $returnAgentsInsteadOfUsers = false, $getIdsOnly = false)
    {
        $agent = Agent::withDepth()->where('id', $agentId)->first();

        if (!$agent) {
            return collect();
        }

        $subAgentsQuery = Agent::withDepth()
           ->where($agent->getLftName(), $includeSelf ? '>=' : '>', $agent->_lft)
           ->where($agent->getRgtName(), $includeSelf ? '<=' : '<', $agent->_rgt);

        if ($depths) {
            foreach ($depths as &$depth)
                $depth+= $agent->depth;

            $subAgentsQuery
                ->whereIn(
                    DB::raw('(select count(1) - 1 from agents i_a where agents._lft between i_a._lft and i_a._rgt)'),
                    $depths
                );
        }

        if ($getIdsOnly) {
            return $subAgentsQuery->where('parent_id', '!=', $agentId)->pluck('id')->all();
        } else if ($returnAgentsInsteadOfUsers) {
            return $subAgentsQuery->get();
        } else {
            $subAgentsQuery->select('user_id');
        }

        return User::whereIn('id', $subAgentsQuery)->get();
    }

    public static function getSubAgents($userId, array $depths = null)
    {
        $agent = Agent::withDepth()->where('user_id', $userId)->first();

        if (!$agent) {
            return collect();
        }

        $subAgentsQuery = Agent::withDepth()
            ->where($agent->getLftName(), '>', $agent->_lft)
            ->where($agent->getRgtName(), '<', $agent->_rgt);

        if ($depths) {
            foreach ($depths as &$depth)
                $depth+= $agent->depth;

            $subAgentsQuery
                ->whereIn(
                    DB::raw('(select count(1) - 1 from agents i_a where agents._lft between i_a._lft and i_a._rgt)'),
                    $depths
                );
        }

        return $subAgentsQuery->get();
    }

    public static function getRewardsByDepth()
    {
        $result = [];
        for ($i = 1; $i <= self::getNetworkDepth(); $i++) {
            $result[$i] = 0;
        }

        /*
        $rootAgents = Agent::with('user')->whereNull('parent_id')->get();
        foreach ($rootAgents as $rootAgent) {
            $data = self::getData($rootAgent->user_id);

            foreach ($data as $userId => $item) {
                foreach ($item['rounds'] as $round) {
                    $result[$round['distance']]+= $round['statistics']['rewardAmount'];
                }
            }
        }
        */

        $rootAgents = Agent::with('user')->whereNull('parent_id')->get();

        foreach ($rootAgents as $rootAgent) {
            foreach (self::getSubAgents($rootAgent->user_id) as $subAgent) {
                $topLevelAgents = Agent::withDepth()
                    ->where('status', 'active')
                    ->whereAncestorOf($subAgent)
                    ->having('depth', '<=', $subAgent->depth)
                    ->orderBy('depth', 'desc')
                    ->take(self::getNetworkDepth())
                    ->get();

                foreach (self::$betsBankCalculators as $calculatorClass) {
                    $calculatorClass::calculate($subAgent->user_id, function($round, $roundStats) use (&$result, $subAgent, $topLevelAgents) {
                        foreach ($topLevelAgents as $topLevelAgent) {
                            $percent = self::getRewardPercent($subAgent, $topLevelAgent);

                            $result[$subAgent->depth - $topLevelAgent->depth]+= $roundStats->bets_bank_total_default_amount / 100 * $percent;
                        }
                    }, null, false);
                }
            }
        }

        return $result;
    }
}