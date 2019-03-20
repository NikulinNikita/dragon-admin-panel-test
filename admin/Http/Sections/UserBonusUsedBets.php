<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\AdminGetterInputMetaData;
use Admin\ColumnMetas\MorphMetaData;
use Admin\ColumnMetas\RelationsMetaData;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;

class UserBonusUsedBets extends BaseSection
{
    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $userBonusId = array_get($scopes, 'user_bonus_id');
        $userBonusId = $userBonusId ?? (request()->get('user_bonus_id') ? request()->get('user_bonus_id') : false);

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->setApply(function ($query) use ($userBonusId, $model) {
            if ($userBonusId) {
                $query->where("{$model->getTable()}.user_bonus_id", $userBonusId);
            }
        });

        $display->setParameters(['user_bonus_id' => $userBonusId]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['bet']);

        $columns = [
            AdminColumn::sText('bet_type', $model),
            AdminColumn::sRelatedLink('bet.id', $model)->setOrderable(true)->setMetaData(MorphMetaData::class),
            AdminColumn::sText('bet.AdminAmount', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('bet.AdminDefaultAmount', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('bet.created_at', $model)->setWidth('150px'),
            AdminColumn::sText('bet.status', $model)->setMetaData(BaseMetaData::class)
        ];

        return $display->setColumns($columns);
    }
}
