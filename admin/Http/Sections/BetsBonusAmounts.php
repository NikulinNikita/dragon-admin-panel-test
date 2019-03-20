<?php

namespace Admin\Http\Sections;

use Admin\Custom\BetsBankEvaluator;
use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminSection;
use App\Models\BaseModel;
use App\Models\BetsBankAccrual;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use DragonStudio\BonusProgram\Types\BonusTypeBetsAmount;

class BetsBonusAmounts extends BaseSection
{
    protected static $result;
    protected static $userBonusPercent;

    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public static function setColumns($display, $model)
    {
        $display->with(['betsBankAccruals']);

        $dateFrom = request()->get('date_from') ? Carbon::parse(request()->get('date_from')) :
            Carbon::parse(Setting::where('key', 'reports_operational_start_date')->first()->value);
        $dateTo   = request()->get('date_to') ? Carbon::parse(request()->get('date_to')) : Carbon::tomorrow();
        $display->setColumns([
            AdminColumn::sText('id', '#'),
            AdminColumn::sLink('name', $model),
            AdminColumn::sCustom('BetsBankTotalAmount', $model, function (BaseModel $item) use ($dateFrom, $dateTo) {
                $accruals     = $item->betsBankAccruals->where('created_at', '>=', $dateFrom)->where('created_at', '<', $dateTo);
                //$result       = count($accruals) ? $accruals->sum('bets_bank_total_amount') : 0;
                $result       = BetsBankEvaluator::evaluateFromAccruals($accruals);
                self::$result = $result;

                return BaseModel::formatCurrency($item->currency_id, $result);
            }),
            AdminColumn::sCustom('BonusPercent', $model, function (BaseModel $item) use ($dateFrom, $dateTo) {
                /*$totalBetsBank          = BetsBankAccrual::where('user_id', $item->id)->sum('bets_bank_total_default_amount');
                $userBonusPercent       = (new BonusTypeBetsAmount())->getBonusPercent($item, $totalBetsBank);
                self::$userBonusPercent = $userBonusPercent;

                return $userBonusPercent;*/
                return self::$userBonusPercent = BetsBankEvaluator::getBonusCashbackPercent();
            }),
            AdminColumn::sCustom('BonusAmount', $model, function (BaseModel $item) use ($dateFrom, $dateTo) {
                $accruals = $item->betsBankAccruals->where('created_at', '>=', $dateFrom)->where('created_at', '<', $dateTo);
                /*$result   = count($accruals) ? $accruals->sum('bets_bank_total_amount') : 0;
                if ($result) {
                    $result = $result / 100 * self::$userBonusPercent;
                }
                self::$result = $result;*/
                self::$result = $result = BetsBankEvaluator::getBonusAmount(self::$result);

                return BaseModel::formatCurrency($item->currency_id, $result);
            }),

            AdminColumn::sCustom('UsedBonusAmount', $model, function (BaseModel $item) use ($dateFrom, $dateTo) {
                $accruals = $item->betsBankAccruals->where('created_at', '>=', $dateFrom)->where('created_at', '<', $dateTo);
                $result   = count($accruals) ? $accruals->where('used_at', '!=', null)->sum('bets_bank_total_amount') : 0;
                if ($result) {
                    $result = $result / 100 * self::$userBonusPercent;
                }
                self::$result = $result;

                return BaseModel::formatCurrency($item->currency_id, $result);
            }),
            AdminColumn::sCustom('BetsBankTotalDefaultAmount', $model, function (BaseModel $item) use ($dateFrom, $dateTo) {
                return BaseModel::convertToDefaultCurrencyAndFormat($item->currency_id, self::$result);
            }),
            AdminColumn::sCustom('BonusDefaultAmount', $model, function (BaseModel $item) use ($dateFrom, $dateTo) {
                return BaseModel::convertToDefaultCurrencyAndFormat($item->currency_id, self::$result);
            }),
            AdminColumn::sCustom('UsedBonusDefaultAmount', $model, function (BaseModel $item) use ($dateFrom, $dateTo) {
                return BaseModel::convertToDefaultCurrencyAndFormat($item->currency_id, self::$result);
            }),
            AdminColumn::sCustom('BonusTillBalance', $model, function (BaseModel $item) {
                return BaseModel::formatCurrency($item->currency_id, $item->BonusTillBalance);
            }),
            AdminColumn::sCustom('BonusOperationsHistory', $model, function (BaseModel $item) use ($dateFrom, $dateTo) {
                $route = BaseModel::generateUrl('BetsBankAccrual', ['user_id' => $item->id, 'date_from' => $dateFrom, 'date_to' => $dateTo]);

                return "<a class='btn btn-xs text-center btn-primary' href='{$route}'><i class='fa fa-eye'></i></a>";
            })->setShowTags(true),
        ]);

        return $display;
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $display = AdminSection::getModel(User::class)->fireDisplay(['scopes' => ['displayType' => 'bets_bonus_amounts']]);
        $display->setHtmlAttribute('class', 'table-default table-hover b-remove_header')->setOrder([[0, 'desc']]);
        $display->getColumns()->disableControls();

        $tabs   = AdminDisplay::tabbed();
        $search = AdminForm::panel()->setView(view('admin::search.bets_bonus_amounts'));
        $tabs->appendTab($display, trans("admin/{$table}.tabs.List"))->setIcon('<i class="fa fa-info"></i>');
        $tabs->appendTab($search, trans("admin/{$table}.tabs.Search"))->setIcon('<i class="fa fa-info"></i>');

        return $tabs;
    }
}
