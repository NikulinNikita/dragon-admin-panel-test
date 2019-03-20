<?php

namespace Admin\Custom\PartnerProgram\OppositeBets;

use App\Models\BaccaratBet;
use App\Models\BaccaratResultReward;
use App\Models\BaccaratRound;
use App\Models\User;
use Illuminate\Support\Collection;

class BaccaratOppositeBets
{
    protected static $oppositeBetPositions = [1, 2];

    protected static $positionRewardCoefficients;


    public static function betHasOppositeBets(BaccaratBet $bet, array $roundResultsPool): bool
    {
        $betPositionOppositeToCurrent = 1 == $bet->baccarat_result_id ? 2 : 1;

        $condition1 = in_array($bet->baccarat_result_id, self::$oppositeBetPositions);
        $condition2 = isset($roundResultsPool[$betPositionOppositeToCurrent]);

        return $condition1 && $condition2;
    }

    public static function calculateBetBankAmount(BaccaratBet $bet, array $roundResultsPool): float
    {
        if (in_array($bet->baccarat_result_id, self::$oppositeBetPositions)) {
            return 0;
        }

        return false; // let upper process calculate bet bank regular way
    }

    public static function generateRoundResultsPool(BaccaratRound $round, Collection $bets)
    {
        $result = [];

        foreach ($bets as $bet) {
            if (!isset($result[$bet->baccarat_result_id])) {
                $result[$bet->baccarat_result_id] = 0;
            }
            $result[$bet->baccarat_result_id]+= $bet->default_amount;
        }

        return $result;
    }

    private static function getPositionRewardCoefficient($positionId)
    {
        if (is_null(self::$positionRewardCoefficients)) {
            self::$positionRewardCoefficients = BaccaratResultReward::whereIn('baccarat_result_id', self::$oppositeBetPositions)
                ->pluck('coefficient', 'baccarat_result_id')
                ->toArray();
        }

        return self::$positionRewardCoefficients[$positionId];
    }
}