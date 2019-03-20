<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\TranslationMetaData;
use Admin\Facades\AdminDisplayTabbedComponent;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class Currencies extends BaseSection
{
    public $canCreate = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'asc']]);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('title', $model)->setMetaData(TranslationMetaData::class),
            AdminColumn::sText('code', $model),
            AdminColumn::sText('symbol', $model),
            AdminColumnEditable::sSelect('fmt_dec_point', $model)->setEnum(config('selectOptions.currencies.delimiter')),
            AdminColumn::sText('fmt_decimals', $model),
            AdminColumnEditable::sSelect('fmt_thousands_sep', $model)->setEnum(config('selectOptions.currencies.delimiter')),
            AdminColumnEditable::sSelect('fmt_symbol_placement', $model)->setEnum(array_combine(config('selectOptions.currencies.fmt_symbol_placement'),
                config('selectOptions.currencies.fmt_symbol_placement'))),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('code', $model)->setValidationRules(['min:3|max:3|required|_unique']),
                    AdminFormElement::sText('symbol', $model)->setValidationRules(['min:1|max:10|required']),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')
                                    ->setValidationRules(['required']),
                ],
                [
                    AdminDisplayTabbedComponent::getTranslations(['title']),
                    AdminFormElement::sSelect('fmt_dec_point', $model)->setEnum(config('selectOptions.currencies.delimiter'))->setValidationRules(['required']),
                    AdminFormElement::sText('fmt_decimals', $model)->setValidationRules(['min:1|max:3|integer|required']),
                    AdminFormElement::sSelect('fmt_thousands_sep', $model)->setEnum(config('selectOptions.currencies.delimiter'))
                                    ->setValidationRules(['required']),
                    AdminFormElement::sSelect('fmt_symbol_placement', $model)->setEnum(array_combine(config('selectOptions.currencies.fmt_symbol_placement'),
                        config('selectOptions.currencies.fmt_symbol_placement')))->setValidationRules(['required']),
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
