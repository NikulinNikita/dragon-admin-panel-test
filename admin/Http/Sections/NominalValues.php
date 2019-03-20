<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\Currency;
use App\Models\Nominal;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class NominalValues extends BaseSection
{
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'nominal_id')) {
                $query->where("{$model->getTable()}.nominal_id", array_get($scopes, 'nominal_id'));
            }
        });
        $display->setParameters(['nominal_id' => array_get($scopes, 'nominal_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['nominal', 'currency']);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
            AdminColumnEditable::sText('value', $model),
        ]);

        if ( ! $scopes) {
            $display->setColumns([
                AdminColumn::sRelatedLink('nominal.color', $model)->setOrderable(true),
                AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
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

        $nominalValue = AdminForm::panel();
        $nominalValue->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('value', $model)->setValidationRules(['required']),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')
                                    ->setValidationRules(['required']),
                ],
                [
                    AdminFormElement::sSelect('currency_id', $model, Currency::class)->setDisplay('code')->setValidationRules(['required']),
                    AdminFormElement::sSelect('nominal_id', $model, Nominal::class)->setDisplay('color'),
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        return $nominalValue;
    }
}
