<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\AdminGetterInputMetaData;
use Admin\ColumnMetas\DateTimeMetaData;
use Admin\ColumnMetas\MorphMetaData;
use Admin\ColumnMetas\RelationsMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use App\Models\Currency;
use App\Models\UserBank;

class UserBankAccountOperations extends BaseSection
{
    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'user_bank_account_id')) {
                $query->where("{$model->getTable()}.user_bank_account_id", array_get($scopes, 'user_bank_account_id'));
            }
        });
        $display->setParameters(['user_bank_account_id' => array_get($scopes, 'user_bank_account_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['userBankAccount.userBank', 'operatable', 'currency']);

        $columnFilters = [
            null,
            null, // AdminColumnFilter::sSelect(UserBank::class, 'title')->setColumnName('userBankAccount.userBank.title')->multiple(),
            AdminColumnFilter::sSelect(Currency::class, 'code')->setColumnName('currency_id')->multiple(),
            AdminColumnFilterComponent::rangeInput(),
            AdminColumnFilterComponent::rangeInput(),
            AdminColumnFilter::sSelect()->setEnum(config('selectOptions.bank_account_operations.operatable_type'))->multiple(),
            null,
            AdminColumnFilterComponent::rangeDate(),
        ];
        if ( ! $scopes) {
            array_splice($columnFilters, 2, 0, [
                AdminColumnFilter::text('userBankAccount.number')->setOperator('contains'),
            ]);
        }
        $display->setColumnFilters($columnFilters)->setPlacement('table.header');

        $columns = [
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('userBankAccount.userBank.title', $model)->setOrderable(true)->setMetaData(RelationsMetaData::class),
            AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
            AdminColumn::sText('AdminValue', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('AdminBalance', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('operatable_type', $model),
            AdminColumn::sRelatedLink('operatable.id', $model)->setOrderable(true)->setMetaData(MorphMetaData::class),
            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
        ];
        if ( ! $scopes) {
            array_splice($columns, 2, 0, [
                AdminColumn::sRelatedLink('userBankAccount.number', $model)->setOrderable(true),
            ]);
        } else {
            $display->setHtmlAttribute('class', 'b-remove_header');
//            $display->getColumns()->disableControls();
        }

        return $display->setColumns($columns);
    }
}
