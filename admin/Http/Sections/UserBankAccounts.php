<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use AdminColumn;
use AdminColumnEditable;
use AdminColumnFilter;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Models\Currency;
use App\Models\UserBank;
use App\Models\UserBankAccountOperation;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class UserBankAccounts extends BaseSection
{
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'user_bank_id')) {
                $query->where("{$model->getTable()}.user_bank_id", array_get($scopes, 'user_bank_id'));
            }
        });
        $display->setParameters(['user_bank_id' => array_get($scopes, 'user_bank_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['userBank', 'currency', 'userBankAccountOperations']);

//        $userBanksArr = UserBank::selectRaw("user_banks.id AS `k`")->selectRaw("CONCAT(user_banks.title, ' - ', 'User',user_banks.user_id) AS `v`")
//                                                                   ->pluck('v', 'k')->all();
        if ( ! $scopes) {
            $columnFilters = [
                null,
                AdminColumnFilter::text('number')->setOperator('contains'),
                null,
                AdminColumnFilter::sSelect(Currency::class, 'code')->setColumnName('currency_id')->multiple(),
                null,
                null,
                null,
                AdminColumnFilter::sSelect()->setEnum(config('selectOptions.common.status'))->multiple(),

            ];
            if ( ! $scopes) {
                array_splice($columnFilters, 1, 0, [
                    AdminColumnFilter::sSelect(UserBank::class, 'title')->setColumnName('user_bank_id')->multiple()
//                    ->setJoins([["users", "user_banks.user_id", "=", "users.id", "left"]])
                    ->setSelectRaws(["user_banks.title, ' - ', 'User',user_banks.user_id"]),
                ]);
            }
            $display->setColumnFilters($columnFilters)->setPlacement('table.header');
        }

        $columns = [
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumnEditable::sText('number', $model),
            AdminColumnEditable::sText('fee', $model),
            AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
            AdminColumn::sText('BankAccountOperationsBalanceFormatted', $model)->setOrderable(false),
            AdminColumnEditable::sText('description', $model),
            AdminColumnEditable::sText('notes', $model),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
        ];
        if ( ! $scopes) {
            array_splice($columns, 1, 0, [
                AdminColumn::sRelatedLink('AdminUserBankId', $model)->setOrderable(true)->setMetaData(BaseMetaData::class),
            ]);
        } else {
            $display->getColumns()->disableControls();
            $display->paginate(9999);
        }

        return $display->setColumns($columns);
    }

    public function onEdit($id)
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $bankAccount = AdminForm::panel();
        $bankAccount->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('number', $model)->required()->addValidationRule('min:1')->addValidationRule('max:191'),
                    AdminFormElement::sSelect('user_bank_id', $model, UserBank::class)->setDisplay('title')->required()->addValidationRule('integer'),
                    AdminFormElement::sSelect('currency_id', $model, Currency::class)->setDisplay('code')->required()->addValidationRule('integer'),
                    AdminFormElement::sText('fee', $model)->required(),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')->required(),
                ],
                [
                    AdminFormElement::sTextArea('description', $model)->setRows(5),
                    AdminFormElement::sTextArea('notes', $model)->setRows(5),
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        $bankAccountOperations = AdminSection::getModel(UserBankAccountOperation::class)->fireDisplay(['scopes' => ['user_bank_account_id' => $id]]);

        $tabs = AdminDisplay::tabbed();
        $tabs->appendTab($bankAccount, trans("admin/{$table}.tabs.UserBankAccount"))->setIcon('<i class="fa fa-info"></i>');
        if ( ! is_null($id)) {
            $tabs->appendTab($bankAccountOperations, trans("admin/{$table}.tabs.UserBankAccountOperations"))->setIcon('<i class="fa fa-credit-card"></i>');
        }

        return $tabs;
    }
}
