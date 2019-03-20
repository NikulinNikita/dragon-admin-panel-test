<?php

namespace Admin\Custom;

use App\Models\ExchangeRate;
use App\Models\Operation;
use App\Models\User;
use App\Models\UserTill;
use App\Events\User\UserTillBalanceChange;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Billing
{
    public static function operation(UserTill $userTill, $amount, $operatableType, $operatableId)
    {
        DB::transaction(function() use ($userTill, $amount, $operatableType, $operatableId) {
            $operation = self::createOperation($userTill, $amount, $operatableType, $operatableId);

            self::changeBalance($operation->userTill->user_id, $operation->userTill->till_id, $amount);
        });
    }

    protected static function createOperation($userTill, $amount, $operatableType, $operatableId)
    {
        $operation = new Operation();

        $operation->user_till_id = $userTill->id;
        $operation->staff_id = Auth::check() ? Auth::user()->id : null;

        $operation->amount = $amount;
        $operation->balance = $userTill->balance + $amount;

        $operation->default_amount = self::convertToDefaultCurrency($operation->amount, $userTill->currency_id);
        $operation->default_balance = self::convertToDefaultCurrency($operation->balance, $userTill->currency_id);

        $operation->operatable_type = $operatableType;
        $operation->operatable_id = $operatableId;

        $operation->save();

        return $operation;
    }

    public static function changeBalance($userId, $tillId, $amount)
    {
        $userTill = UserTill::where(['user_id' => $userId, 'till_id' => $tillId])->first();

        if (!$userTill) {
            throw new \Exception('Till not found', 500);
        }

        DB::transaction(function() use ($userTill, $amount) {
            $userTill->balance+= $amount;
            $userTill->default_amount = self::convertToDefaultCurrency($userTill->balance, $userTill->currency_id);

            $userTill->save();

            if (User::MONEY_TILL_ID == $userTill->till_id) {
                self::broadcastBalanceChangeEvent($userTill);
            }
        });
    }

    public static function convertToDefaultCurrency($amount, $currencyId): float
    {
        $exchangeRate = ExchangeRate::where('status', 'active')
            ->where('currency_id', $currencyId)
            ->first();

        return $amount / $exchangeRate->rate;
    }

    protected static function broadcastBalanceChangeEvent($userTill)
    {
        broadcast(new UserTillBalanceChange($userTill));
    }
}
