<?php

namespace Admin\Custom\PartnerProgram;

use App\Models\BaccaratBet;
use App\Models\BaccaratRound;
use App\Models\BaseModel;
use App\Models\RouletteBet;
use App\Models\RouletteRound;
use App\Models\User;
use Admin\Custom\PartnerProgram\OppositeBets\BaccaratOppositeBets;
use Admin\Custom\PartnerProgram\OppositeBets\RouletteOppositeBets;
use Illuminate\Support\Collection;

class OppositeBetsHandler
{
    public static function betHasOppositeBets(BaseModel $bet, $betTypeSpecificData = null): bool
    {
        switch (true) {
            case $bet instanceOf BaccaratBet:
                return BaccaratOppositeBets::betHasOppositeBets($bet, $betTypeSpecificData);
            case $bet instanceOf RouletteBet:
                return RouletteOppositeBets::betHasOppositeBets($bet, $betTypeSpecificData);
        }

        return false;
    }

    public static function calculateBetBankAmount(BaseModel $bet, $betTypeSpecificData = null): float
    {
        switch (true) {
            case $bet instanceOf BaccaratBet:
                return BaccaratOppositeBets::calculateBetBankAmount($bet, $betTypeSpecificData);
            case $bet instanceOf RouletteBet:
                return RouletteOppositeBets::calculateBetBankAmount($bet, $betTypeSpecificData);
        }

        return 0;
    }

    public static function generateRoundResultsPool(BaseModel $round, Collection $bets)
    {
        switch (true) {
            case $round instanceOf BaccaratRound:
                return BaccaratOppositeBets::generateRoundResultsPool($round, $bets);
            case $round instanceOf RouletteRound:
                return RouletteOppositeBets::generateRoundResultsPool($round, $bets);
        }
    }
}