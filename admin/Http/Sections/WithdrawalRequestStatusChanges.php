<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\RelationsMetaData;
use AdminColumn;
use AdminDisplay;

class WithdrawalRequestStatusChanges extends BaseSection
{
    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header_and_pagination');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'withdrawal_request_id')) {
                $query->where("{$model->getTable()}.withdrawal_request_id", array_get($scopes, 'withdrawal_request_id'));
            }
        });
        $display->setParameters(['withdrawal_request_id' => array_get($scopes, 'withdrawal_request_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['staff']);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('staff.name', $model)->setOrderable(true)->setMetaData(RelationsMetaData::class),
            AdminColumn::sText('status', $model),
            AdminColumn::sText('created_at', $model),
        ]);

        if ( ! $scopes) {
            $display->setColumns([
                AdminColumn::sText('withdrawal_request_id', $model),
            ]);
        } else {
            $display->getColumns()->disableControls();
        };

        return $display;
    }
}
