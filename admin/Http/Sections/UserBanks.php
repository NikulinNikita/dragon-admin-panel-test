<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Models\User;
use App\Models\UserBankAccount;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\Save;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class UserBanks extends BaseSection
{
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['user']);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true),
            AdminColumn::sText('title', $model),
            AdminColumnEditable::sText('address', $model),
            AdminColumnEditable::sText('description', $model),
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
                    AdminFormElement::sText('title', $model)->required()->addValidationRule('min:3')->addValidationRule('max:191'),
                    AdminFormElement::sText('address', $model)->addValidationRule('max:191'),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')->required(),
                ],
                [
                    AdminFormElement::sSelect('user_id', $model, User::class)->setDisplay('name')->required()->addValidationRule('integer'),
                    AdminFormElement::sTextArea('description', $model)->setRows(5),
                    AdminFormElement::sTextArea('notes', $model)->setRows(5),
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => ! is_null($id) ? new SaveAndClose() : new Save(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        $bankAccounts = AdminSection::getModel(UserBankAccount::class)->fireDisplay(['scopes' => ['user_bank_id' => $id]]);

        $tabs = AdminDisplay::tabbed();
        $tabs->appendTab($bank, trans("admin/{$table}.tabs.UserBank"))->setIcon('<i class="fa fa-info"></i>');
        if ( ! is_null($id)) {
            $tabs->appendTab($bankAccounts, trans("admin/{$table}.tabs.UserBankAccounts"))->setIcon('<i class="fa fa-credit-card"></i>');
        }

        return $tabs;
    }
}
