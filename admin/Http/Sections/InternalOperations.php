<?php

namespace Admin\Http\Sections;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\BankAccount;
use App\Models\BankAccountOperation;
use App\Models\Staff;
use DB;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class InternalOperations extends BaseSection
{
    public $canDelete = false;

    public function initialize()
    {
        $this->created(function ($config, Model $model) {
            DB::beginTransaction();
            try {
                $bankAccountsIds = [];
                $model->from_bank_account_id ? array_push($bankAccountsIds, $model->from_bank_account_id) : false;
                $model->to_bank_account_id ? array_push($bankAccountsIds, $model->to_bank_account_id) : false;
                $bankAccounts = BankAccount::whereIn('id', $bankAccountsIds)->get();

                $this->checkIf(count($bankAccountsIds) !== count($bankAccounts), "Funds can not be transfered to the same Bank Account!");
                $this->checkIf(count($bankAccounts) > 1 && $bankAccounts->first()->currency_id !== $bankAccounts->last()->currency_id,
                    "Funds can not be transfered to the Bank Account with different currency!");


                BankAccountOperation::makeItemWithRelationsForInternalOperations($model);
            } catch (\Exception $e) {
                DB::rollback();
                $model->delete();
                throw $e;
            }
            DB::commit();
        });
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'from_bank_account_id')) {
                $query->where("{$model->getTable()}.from_bank_account_id", array_get($scopes, 'from_bank_account_id'));
            }
            if (array_get($scopes, 'to_bank_account_id')) {
                $query->where("{$model->getTable()}.to_bank_account_id", array_get($scopes, 'to_bank_account_id'));
            }
        });
        $display->setParameters(['from_bank_account_id' => array_get($scopes, 'from_bank_account_id')]);
        $display->setParameters(['to_bank_account_id' => array_get($scopes, 'to_bank_account_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['fromBankAccount', 'toBankAccount', 'currency']);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('fromBankAccount.number', $model)->setOrderable(true),
            AdminColumn::sRelatedLink('toBankAccount.number', $model)->setOrderable(true),
            AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
            AdminColumn::sText('amount', $model),
            AdminColumn::sText('default_amount', $model),
            AdminColumn::sText('type', $model),
            AdminColumn::sText('comment', $model),
            AdminColumn::sText('created_at', $model),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();
        $bank_account_id = request()->get('bank_account_id');

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sSelect('staff_id', $model, Staff::class)->setDisplay('name')->setDefaultValue(auth()->user()->id)
                                    ->setReadonly(true)->required(),
                    AdminFormElement::sRadio('type', $model)->setEnum(config('selectOptions.internal_operations.type'))->required(),
                    AdminFormElement::sSelect('from_bank_account_id', $model, BankAccount::class)->setDefaultValue($bank_account_id)
                                    ->setWith(['currency', 'bank', 'bankAccountOperations'])->setDisplay('AdminCurrencyAndBankNumber')
                                    ->nullable()->setValidationRules(['required_without:to_bank_account_id'])->setLimit(0),
                    AdminFormElement::sSelect('to_bank_account_id', $model, BankAccount::class)->setDefaultValue($bank_account_id)
                                    ->setWith(['currency', 'bank', 'bankAccountOperations'])->setDisplay('AdminCurrencyAndBankNumber')
                                    ->nullable()->setValidationRules(['required_without:from_bank_account_id'])->setLimit(0),
                    AdminFormElement::sText('amount', $model)->setValidationRules(['numeric|required']),
                ],
                [
                    AdminFormElement::sTextArea('comment', $model),
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => is_null($id) ? new SaveAndClose() : null,
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        return $form;
    }
}
