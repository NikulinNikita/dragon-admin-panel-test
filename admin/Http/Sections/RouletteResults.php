<?php

namespace Admin\Http\Sections;

use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use App\Models\BaseModel;

class RouletteResults extends BaseSection
{
    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $relationId = array_get($scopes, 'table_limit_id');
        $display    = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header_and_pagination');
        $display->setApply(function ($query) use ($scopes, $relationId, $model) {
            if ($relationId) {
                $query->whereHas('resultLimitCurrencies.tableLimitCurrency.tableLimit', function ($query) use ($relationId) {
                    return $query->where("table_limit_id", $relationId);
                });
            }
        });
        $display->setParameters(['table_limit_id' => array_get($scopes, 'table_limit_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'asc']]);
        $display->with(['resultLimitCurrencies.tableLimitCurrency.currency', 'resultLimitCurrencies.tableLimitCurrency.tableLimit', 'rouletteResultRewards']);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('code', $model),
            AdminColumn::sCustom('Coefficients', $model, function (BaseModel $model) {
                $result = '';
                foreach ($model->rouletteResultRewards as $k => $rouletteResultReward) {
                    $result = $result . "<li><span class=\"label label-info\">{$rouletteResultReward->code} {$rouletteResultReward->coefficient}</span></li>";
                }

                return $result;
            })->setHtmlAttribute('class', 'custom-list-items')->setWidth('60px')->setShowTags(true),
            AdminColumnEditable::sText('AdminRouletteResult_USD.min_limit', 'USD min')->setOrderable(false)
                               ->setUrl(route('admin.updateColumnEditable', ["table_limit_id" => $relationId])),
            AdminColumnEditable::sText('AdminRouletteResult_USD.max_limit', 'USD max')->setOrderable(false)
                               ->setUrl(route('admin.updateColumnEditable', ["table_limit_id" => $relationId])),
            AdminColumnEditable::sText('AdminRouletteResult_CNY.min_limit', 'CNY min')->setOrderable(false)
                               ->setUrl(route('admin.updateColumnEditable', ["table_limit_id" => $relationId])),
            AdminColumnEditable::sText('AdminRouletteResult_CNY.max_limit', 'CNY max')->setOrderable(false)
                               ->setUrl(route('admin.updateColumnEditable', ["table_limit_id" => $relationId])),
            AdminColumnEditable::sText('AdminRouletteResult_KRW.min_limit', 'KRW min')->setOrderable(false)
                               ->setUrl(route('admin.updateColumnEditable', ["table_limit_id" => $relationId])),
            AdminColumnEditable::sText('AdminRouletteResult_KRW.max_limit', 'KRW max')->setOrderable(false)
                               ->setUrl(route('admin.updateColumnEditable', ["table_limit_id" => $relationId])),
            AdminColumnEditable::sText('AdminRouletteResult_RUB.min_limit', 'RUB min')->setOrderable(false)
                               ->setUrl(route('admin.updateColumnEditable', ["table_limit_id" => $relationId])),
            AdminColumnEditable::sText('AdminRouletteResult_RUB.max_limit', 'RUB max')->setOrderable(false)
                               ->setUrl(route('admin.updateColumnEditable', ["table_limit_id" => $relationId])),
            AdminColumnEditable::sText('AdminRouletteResult_UAH.min_limit', 'UAH min')->setOrderable(false)
                               ->setUrl(route('admin.updateColumnEditable', ["table_limit_id" => $relationId])),
            AdminColumnEditable::sText('AdminRouletteResult_UAH.max_limit', 'UAH max')->setOrderable(false)
                               ->setUrl(route('admin.updateColumnEditable', ["table_limit_id" => $relationId])),
            AdminColumnEditable::sSelect('AdminRouletteResult_ALL.status', $model)->setEnum(config('selectOptions.common.status'))
                               ->setOrderable(false)->setUrl(route('admin.updateColumnEditable', ["table_limit_id" => $relationId])),
        ]);

        if ( ! $scopes) {
            $display->setColumns([
//                AdminColumn::sRelatedLink('tableLimitCurrency.tableLimit.title', $model)->setOrderable(true),
            ]);
        } else {
            $display->paginate(9999);
        }
        $display->getColumns()->disableControls();

        return $display;
    }
}
