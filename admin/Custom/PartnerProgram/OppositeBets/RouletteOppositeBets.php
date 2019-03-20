<?php

namespace Admin\Custom\PartnerProgram\OppositeBets;

use App\Models\RouletteBet;
use App\Models\RouletteResultPreset;
use App\Models\RouletteRound;
use App\Models\User;
use Illuminate\Support\Collection;

class RouletteOppositeBets
{
    protected static $oppositeBetsResultGroups = [
        // code + index => length
        [
            'column-0' => 12,
            'column-1' => 12,
            'column-2' => 12
        ],
        [
            'dozen-0' => 12,
            'dozen-1' => 12,
            'dozen-2' => 12
        ],
        [
            'red-0' => 18,
            'black-0' => 18
        ],
        [
            'odd-0' => 18,
            'even-0' => 18
        ],
        [
            'low-0' => 18,
            'high-0' => 18
        ],
        [
            'straight-0' => 1,
            'straight-1' => 1,
            'straight-2' => 1,
            'straight-3' => 1,
            'straight-4' => 1,
            'straight-5' => 1,
            'straight-6' => 1,
            'straight-7' => 1,
            'straight-8' => 1,
            'straight-9' => 1,
            'straight-10' => 1,
            'straight-11' => 1,
            'straight-12' => 1,
            'straight-13' => 1,
            'straight-14' => 1,
            'straight-15' => 1,
            'straight-16' => 1,
            'straight-17' => 1,
            'straight-18' => 1,
            'straight-19' => 1,
            'straight-20' => 1,
            'straight-21' => 1,
            'straight-22' => 1,
            'straight-23' => 1,
            'straight-24' => 1,
            'straight-25' => 1,
            'straight-26' => 1,
            'straight-27' => 1,
            'straight-28' => 1,
            'straight-29' => 1,
            'straight-30' => 1,
            'straight-31' => 1,
            'straight-32' => 1,
            'straight-33' => 1,
            'straight-34' => 1,
            'straight-35' => 1,
            'straight-36' => 1
        ]
    ];

    private static $resultPresets;


    public static function betHasOppositeBets(RouletteBet $bet, array $roundResultsPool): bool
    {
        $resultPreset = self::getResultPreset($bet->roulette_result_preset_id);

        $poolKey = self::composePoolKey($resultPreset);

        foreach (self::$oppositeBetsResultGroups as $resultsIds) {
            if (isset($resultsIds[$poolKey])) {
                $presetIdsInGroup = $resultsIds;
                unset($presetIdsInGroup[$poolKey]);
                $presetIdsInGroup = array_keys($presetIdsInGroup);

                foreach ($presetIdsInGroup as $presetKey) {
                    if (!isset($roundResultsPool[$presetKey])) {
                        continue 2;
                    }
                }

                return true;
            }
        }

        return false;
    }

    public static function calculateBetBankAmount(RouletteBet $bet, array $roundResultsPool): float
    {
        $resultPreset = self::getResultPreset($bet->roulette_result_preset_id);

        $poolKey = self::composePoolKey($resultPreset);

        foreach (self::$oppositeBetsResultGroups as $resultIds) {
            if (!isset($resultIds[$poolKey])) {
                continue;
            }

            // check #1. betting should cover 36 cells or more
            $bettingNumbersCount = 0;
            foreach ($resultIds as $poolKey => $numbersCount) {
                if (isset($roundResultsPool[$poolKey])) {
                    $bettingNumbersCount+= $numbersCount;
                }
            }

            if ($bettingNumbersCount >= 36) {
                return 0;
            }
        }

        return false;
    }

    public static function generateRoundResultsPool(RouletteRound $round, Collection $bets)
    {
        $result = [];

        foreach ($bets as $bet) {
            $poolKey = self::composePoolKey(self::getResultPreset($bet->roulette_result_preset_id));

            if (!isset($result[$poolKey])) {
                $result[$poolKey] = 0;
            }

            $result[$poolKey]+= $bet->default_amount;
        }

        return $result;
    }

    private static function getResultPreset($id): RouletteResultPreset
    {
        if (is_null(self::$resultPresets)) {
            self::$resultPresets = RouletteResultPreset::with('rouletteResult')
                ->get()
                ->keyBy('id');
        }

        return self::$resultPresets[$id];
    }

    private static function composePoolKey(RouletteResultPreset $resultPreset)
    {
        return $resultPreset->rouletteResult->code . '-' . $resultPreset->index;
    }
}