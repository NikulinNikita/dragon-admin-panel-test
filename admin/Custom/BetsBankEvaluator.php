<?php

namespace Admin\Custom;

use App\Models\BetsBankAccrual;
use DragonStudio\BonusProgram\Types\BonusTypeBetsAmount;

class BetsBankEvaluator
{
    public static function evaluate(int $userId, array $period = null): float
    {
        $query = BetsBankAccrual::where('user_id', $userId);

        if ($period) {
            $query->whereBetween('created_at', $period);
        }

        return self::evaluateFromAccruals($query->get());
    }

    public static function evaluateFromAccruals(iterable $accruals): float
    {
        $result = 0;

        foreach ($accruals as $accrual)
            $result+= self::evaluateFromSingleAccrual($accrual);

        return $result;
    }

    public static function evaluateFromSingleAccrual($accrual): float
    {
        return $accrual->bets_bank_total_amount;
    }

    public static function getBonusAmount(float $betsBankAmount)
    {
        if (!$betsBankAmount) {
            return 0;
        }

        return $betsBankAmount / 100 * self::getBonusCashbackPercent();
    }

    public static function getBonusCashbackPercent()
    {
        return (new BonusTypeBetsAmount())->getBonusRecord()->bonus_amount_percent;
    }

    public static function collectRoundStats($userId, array $period): array
    {
        $betsBankCalculators = [
            'baccarat' => \Admin\Custom\PartnerProgram\BetsBankCalculator\BetsBankCalculatorBaccarat::class,
            'roulette' => \Admin\Custom\PartnerProgram\BetsBankCalculator\BetsBankCalculatorRoulette::class
        ];

        $betsAmount = [];
        $betBankAmount = [];

        foreach ($betsBankCalculators as $gameType => $calculatorClass) {
            $betsAmount[$gameType] = $betBankAmount[$gameType] = 0;

            $calculatorClass::calculate($userId, function($round, $roundStats, $roundBets) use (&$betsAmount, &$betBankAmount, $gameType) {
                $betsAmount[$gameType]+= $roundStats->bets_total_amount;
                $betBankAmount[$gameType]+= $roundStats->bets_bank_total_amount;
            }, $period);
        }

        return [
            $betsAmount['baccarat'] + $betsAmount['roulette'],
            $betBankAmount['baccarat'] + $betBankAmount['roulette'],
        ];
    }
}