<?php

namespace Admin\Custom\PartnerProgram\BetsBankCalculator;

use Closure;
use App\Models\AgentRewardBet;
use App\Models\BaccaratBet;
use App\Models\BaccaratRound;
use App\Models\UserBonusUsedBet;
use App\Models\UserSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class BetsBankCalculatorBaccarat implements BetsBankCalculatorInterface
{
    public static function calculate(int $userId, Closure $cb, array $period = null, bool $fetchRelationships = true)
    {
        foreach (self::fetchRoundsByUserId($userId, $period, $fetchRelationships) as $round) {
            $result = call_user_func($cb, $round, $round->betsBankAccruals->first() ?? null);

            if (false === $result) {
                return;
            }
        }
    }

    protected static function fetchRoundsByUserId($userId, array $period = null, bool $fetchRelationships = true): Collection
    {
        $query = BaccaratRound::select('*')
            ->with(['betsBankAccruals' => function($q) use ($userId) {
                $q->where('user_id', $userId);
            }])
            ->with(['baccaratBets' => function($q) use ($userId) {
                $q->with('userSession.user'/*, 'baccaratResult.baccaratResultReward'*/)
                    ->whereIn('user_session_id', function($q) use ($userId) {
                        $q->select('id')
                            ->from(with(new UserSession())->getTable())
                            ->where('user_id', $userId);
                    })
                    ->whereNotIn('id', function($q) {
                        $q->select('bet_id')
                            ->from(with(new AgentRewardBet())->getTable())
                            ->where('bet_type', 'baccarat_bet');
                    })
                    ->whereNotIn('id', function($q) {
                        $q->select('bet_id')
                            ->from(with(new UserBonusUsedBet())->getTable())
                            ->where('bet_type', 'baccarat_bet');
                    });
            }])
            ->where('status', 'finished')
            ->whereIn('id', function($q) use ($userId, $period) {
                $q->select(DB::raw('distinct baccarat_round_id'))
                    ->from(with(new BaccaratBet())->getTable())
                    ->whereIn('user_session_id', function($q) use ($userId, $period) {
                        $q->select('id')
                            ->from('user_sessions')
                            ->where('user_id', $userId);

                        if ($period) {
                            $q->whereBetween('created_at', $period);
                        }
                    });
            })
            ->orderBy('created_at', 'desc');

        if ($fetchRelationships) {
            $query->with('staffSession.table.game');
        }

        if ($period) {
            $query->where('bet_acception_started_at', '>', (string)$period[0]);
            $query->where('bet_acception_ended_at', '<', (string)$period[1]);
        }

        return $query->get();
    }
};