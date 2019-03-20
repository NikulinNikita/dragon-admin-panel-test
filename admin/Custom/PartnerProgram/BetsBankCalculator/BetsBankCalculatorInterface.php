<?php

namespace Admin\Custom\PartnerProgram\BetsBankCalculator;

use Closure;

interface BetsBankCalculatorInterface
{
    public static function calculate(int $userId, Closure $cb, array $period = null, bool $fetchRelationships = true);
}