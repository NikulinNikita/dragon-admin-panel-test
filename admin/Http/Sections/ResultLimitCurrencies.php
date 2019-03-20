<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\MorphMetaData;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;

class ResultLimitCurrencies extends BaseSection
{
    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->setApply(function ($query) use ($scopes, $model) {
            if ($relationId = array_get($scopes, 'table_limit_id')) {
                $query->whereHas('tableLimitCurrency.tableLimit', function ($query) use ($relationId) {
                    return $query->where("table_limit_id", $relationId);
                });
            }
        });
        $display->setParameters(['table_limit_id' => array_get($scopes, 'table_limit_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'asc']]);
        $display->with(['tableLimitCurrency.currency', 'tableLimitCurrency.tableLimit', 'limitable']);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('tableLimitCurrency.currency.code', $model)->setOrderable(true),
            AdminColumnEditable::sText('min_limit', $model),
            AdminColumnEditable::sText('max_limit', $model),
            AdminColumn::sRelatedLink('limitable.code', $model)->setOrderable(true)->setMetaData(MorphMetaData::class),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
        ]);

        if ( ! $scopes) {
            $display->setColumns([
                AdminColumn::sRelatedLink('tableLimitCurrency.tableLimit.title', $model)->setOrderable(true),
                AdminColumn::sRelatedLink('tableLimitCurrency.id', $model)->setOrderable(true),
            ]);
        } else {
            $this->canCreate = false;
            $this->canEdit   = false;
            $display->getColumns()->disableControls();
            $display->paginate(9999);
        }

        return $display;
    }
}
