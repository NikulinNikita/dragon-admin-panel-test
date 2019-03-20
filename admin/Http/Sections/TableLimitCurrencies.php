<?php

namespace Admin\Http\Sections;

use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\Currency;
use App\Models\TableLimit;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class TableLimitCurrencies extends BaseSection
{
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'table_limit_id')) {
                $query->where("{$model->getTable()}.table_limit_id", array_get($scopes, 'table_limit_id'));
            }
        });
        $display->setParameters(['table_limit_id' => array_get($scopes, 'table_limit_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'asc']]);
        $display->with(['currency', 'tableLimit.tableLimitCurrencies']);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
            AdminColumnEditable::sText('min_limit', $model),
            AdminColumn::sText('AdminAdvisedMinLimit', $model)->setOrderable(false),
            AdminColumnEditable::sText('max_limit', $model),
            AdminColumn::sText('AdminAdvisedMaxLimit', $model)->setOrderable(false),
//            AdminColumnEditable::select('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
        ]);

        if ( ! $scopes) {
            $display->setColumns([
                AdminColumn::sRelatedLink('tableLimit.title', $model)->setOrderable(true),
            ]);
        } else {
            $this->canCreate = false;
            $display->getColumns()->disableControls();
            $display->paginate(9999);
        }

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();

        $tableLimitCurrencies = AdminForm::panel();
        $tableLimitCurrencies->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('min_limit', $model)->setValidationRules(['required']),
                    AdminFormElement::sText('max_limit', $model)->setValidationRules(['required']),
                ],
                [
                    AdminFormElement::sSelect('table_limit_id', $model, TableLimit::class)->setDisplay('title')->setValidationRules(['required']),
                    AdminFormElement::sSelect('currency_id', $model, Currency::class)->setDisplay('code')->setValidationRules(['required']),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')
                                    ->setValidationRules(['required']),
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        return $tableLimitCurrencies;
    }
}
