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
use AdminSection;
use App\Models\BankAccount;
use App\Models\Gateway;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\Save;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class Banks extends BaseSection
{
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['gateway']);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sLink('title', $model)->setMetaData(TranslationMetaData::class),
            AdminColumnEditable::sText('address', $model)->setMetaData(TranslationMetaData::class),
            AdminColumnEditable::sText('description', $model)->setMetaData(TranslationMetaData::class),
            AdminColumn::sText('gateway.title', $model),
            AdminColumnEditable::sText('notes', $model),
            AdminColumnEditable::sText('notes', $model),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $bank = AdminForm::panel();
        $bank->addBody(
            AdminFormElement::columns([
                [
                    ! is_null($id) ?
                        AdminFormElement::sText('slug', $model)->setReadonly(true) :
                        AdminFormElement::hidden('slug'),
                    AdminFormElement::sSelect('gateway_id', $model, Gateway::class)->setDisplay('translations.title')->setValidationRules(['integer|required'])
                        ->setQueryFilters([['gateways.status', 'active']]),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')->required(),
                    AdminFormElement::sTextArea('notes', $model)->setRows(5),
                ],
                [
                    AdminDisplayTabbedComponent::getTranslations(['title', 'address', 'description']),
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => ! is_null($id) ? new SaveAndClose() : new Save(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        $bankAccounts = AdminSection::getModel(BankAccount::class)->fireDisplay(['scopes' => ['bank_id' => $id]]);

        $tabs = AdminDisplay::tabbed();
        $tabs->appendTab($bank, trans("admin/{$table}.tabs.Bank"))->setIcon('<i class="fa fa-info"></i>');
        if ( ! is_null($id)) {
            $tabs->appendTab($bankAccounts, trans("admin/{$table}.tabs.BankAccounts"))->setIcon('<i class="fa fa-credit-card"></i>');
        }

        return $tabs;
    }
}
