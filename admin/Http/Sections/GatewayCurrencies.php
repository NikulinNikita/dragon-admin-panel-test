<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\RelationsWithTranslationMetaData;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\Currency;
use App\Models\Gateway;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class GatewayCurrencies extends BaseSection
{
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'gateway_id')) {
                $query->where("{$model->getTable()}.gateway_id", array_get($scopes, 'gateway_id'));
            }
        });
        $display->setParameters(['gateway_id' => array_get($scopes, 'gateway_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'asc']]);
        $display->with(['gateway', 'currency']);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
            AdminColumnEditable::sText('min_limit', $model),
            AdminColumnEditable::sText('max_limit', $model),
        ]);

        if ( ! $scopes) {
            $display->setColumns([
                AdminColumn::sRelatedLink('gateway.title', $model)->setOrderable(true)->setMetaData(RelationsWithTranslationMetaData::class),
                AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
            ]);
        } else {
            $this->canCreate = false;
            $display->getColumns()->disableControls();
            $display->setHtmlAttribute('class', 'b-remove_header_and_pagination');
            $display->paginate(9999);
        }

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sSelect('gateway_id', $model, Gateway::class)->setDisplay('translations.title')->setValidationRules(['required']),
                    AdminFormElement::sSelect('currency_id', $model, Currency::class)->setDisplay('code')->setValidationRules(['required']),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')
                                    ->setValidationRules(['required']),
                ],
                [
                    AdminFormElement::sText('min_limit', $model)->setValidationRules(['required']),
                    AdminFormElement::sText('max_limit', $model)->setValidationRules(['required']),
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        return $form;
    }
}
