<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\Custom\BetsBankEvaluator;
use AdminColumn;
use AdminDisplay;
use AdminSection;
use App\Models\BaseModel;
use App\Models\BetsBankAccrual;
use DragonStudio\BonusProgram\Types\BonusTypeBetsAmount;

class BetsBankAccruals extends BaseSection
{
    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'user_id') || request()->get('user_id')) {
                $query->where("{$model->getTable()}.user_id", array_get($scopes, 'user_id') ?? request()->get('user_id'));
            }
            if (array_get($scopes, 'date_from') || request()->get('date_from')) {
                $query->where("{$model->getTable()}.created_at", '>=', array_get($scopes, 'date_from') ?? request()->get('date_from'));
            }
            if (array_get($scopes, 'date_to') || request()->get('date_to')) {
                $query->where("{$model->getTable()}.created_at", '<', array_get($scopes, 'date_to') ?? request()->get('date_to'));
            }
            if (array_get($scopes, 'nonUsed') || request()->get('nonUsed')) {
                $query->where("{$model->getTable()}.used_at",  null);
            }
        });
        $display->setParameters(['user_id' => array_get($scopes, 'user_id') ?? request()->get('user_id'),]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['user', 'userSession.table']);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
//            AdminColumn::sRelatedLink('user.name', $model),
            AdminColumn::sText('created_at', $model)->setWidth('150px'),
            AdminColumn::sRelatedLink('userSession.table.title', $model),
//            AdminColumn::sRelatedLink('userSession.seat', $model),
            AdminColumn::sCustom('roundable_id', $model, function (BaseModel $item) {
                $modelClass = BaseModel::makeModelClass($item->roundable_type);
                $route      = AdminSection::getModel($modelClass)->getEditUrl($item->roundable_id);

                return "<a class='btn btn-xs text-center' href='{$route}'>{$item->roundable_id}</a>";
            })->setShowTags(true)->setOrderable(true)->setMetaData(BaseMetaData::class),
            AdminColumn::sText('bets_total_amount', $model),
            AdminColumn::sText('bets_total_profit', $model),
            AdminColumn::sText('bets_bank_total_amount', $model),
            AdminColumn::sText('bets_total_default_amount', $model),
            AdminColumn::sText('bets_total_default_profit', $model),
            AdminColumn::sText('bets_bank_total_default_amount', $model),
            AdminColumn::sCustom('AdminBonusAmount', $model, function (BaseModel $item) {
                return BetsBankEvaluator::getBonusAmount(BetsBankEvaluator::evaluateFromSingleAccrual($item));
            }),
//            AdminColumn::sText('used_at', $model)->setWidth('150px'),
        ]);

        $display->getColumns()->disableControls();

        return $display;
    }
}
