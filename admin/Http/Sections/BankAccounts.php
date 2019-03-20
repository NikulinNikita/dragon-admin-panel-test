<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\RelationsWithTranslationMetaData;
use Admin\ColumnMetas\TranslationMetaData;
use Admin\Facades\AdminDisplayTabbedComponent;
use Admin\Services\User\PersonalNotificationService;
use AdminColumn;
use AdminColumnEditable;
use AdminColumnFilter;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Models\Bank;
use App\Models\BankAccountOperation;
use App\Models\Currency;
use App\Models\User;
use App\Notifications\User\PersonalNotification;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class BankAccounts extends BaseSection
{
    public $canDelete = false;

    public function initialize()
    {
        $this->updated(function ($config, $model) {
            if (request()->get('next_action') == 'save_and_notify') {
                $this->notifyUsers();
            }
        });
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'bank_id')) {
                $query->where("{$model->getTable()}.bank_id", array_get($scopes, 'bank_id'));
            }
        });
        $display->setParameters(['bank_id' => array_get($scopes, 'bank_id')]);
        $display->with(['bank', 'currency', 'bankAccountOperations']);

        if ( ! $scopes) {
            $columnFilters = [
                null,
                AdminColumnFilter::text('number')->setOperator('contains'),
                null,
                null,
                AdminColumnFilter::sSelect(Currency::class, 'code')->setColumnName('currency_id')->multiple(),
                null,
                null,
                null,
                null,
                null,
                null,
                AdminColumnFilter::sSelect()->setEnum(config('selectOptions.common.status'))->multiple(),
            ];
            if ( ! $scopes) {
                array_splice($columnFilters, 2, 0, [
                    AdminColumnFilter::sSelect(Bank::class, 'translations.title')->setColumnName('bank_id')->multiple(),
                ]);
            }
            $display->setColumnFilters($columnFilters)->setPlacement('table.header');
        }

        $columns = [
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumnEditable::sText('name', $model),
            AdminColumnEditable::sText('number', $model),
            AdminColumnEditable::sText('fee', $model),
            AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
            AdminColumn::sText('BankAccountOperationsBalanceFormatted', $model)->setOrderable(false),
            AdminColumn::sText('BankAccountOperationsDefaultBalance', $model)->setOrderable(false),
            AdminColumnEditable::sText('min_limit', $model),
            AdminColumnEditable::sText('max_limit', $model),
            AdminColumnEditable::sCheckbox('visible_for_users', null, null, $model),
            AdminColumnEditable::sText('description', $model)->setMetaData(TranslationMetaData::class),
            AdminColumnEditable::sText('notes', $model),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
        ];
        if ( ! $scopes) {
            array_splice($columns, 2, 0, [
                AdminColumn::sRelatedLink('bank.title', $model)->setOrderable(true)->setMetaData(RelationsWithTranslationMetaData::class),
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
                    AdminFormElement::sText('name', $model)->setValidationRules(['min:3|max:191|required']),
                    AdminFormElement::sText('number', $model)->setValidationRules(['min:3|numeric|required']),
                    AdminFormElement::sText('min_limit', $model)->setValidationRules(['integer|required']),
                    AdminFormElement::sText('max_limit', $model)->setValidationRules(['integer|required']),
                    AdminFormElement::sSelect('bank_id', $model, Bank::class)->setDisplay('translations.title')->setValidationRules(['integer|required']),
                    AdminFormElement::sText('fee', $model)->setValidationRules(['max:100|numeric|required']),
                    AdminFormElement::sSelect('currency_id', $model, Currency::class)->setDisplay('code')->setValidationRules(['integer|required']),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')->required(),
                    AdminFormElement::sCheckbox('visible_for_users', $model),
                ],
                [
                    AdminDisplayTabbedComponent::getTranslations(['description']),
                    AdminFormElement::sTextArea('notes', $model)->setRows(5),
                ]
            ])
        )->getButtons()->setButtons([
            'save'        => new SaveAndClose(),
            'save_notify' => (new \Admin\Form\Element\SaveNotifyButton()),
            'delete'      => new Delete(),
            'cancel'      => new Cancel(),
        ]);

        $bankAccountOperations = AdminSection::getModel(BankAccountOperation::class)->fireDisplay(['scopes' => ['bank_account_id' => $id]]);

        $tabs = AdminDisplay::tabbed();
        $tabs->appendTab($bankAccount, trans("admin/{$table}.tabs.BankAccount"))->setIcon('<i class="fa fa-info"></i>');
        if ( ! is_null($id)) {
            $tabs->appendTab($bankAccountOperations, trans("admin/{$table}.tabs.BankAccountOperations"))->setIcon('<i class="fa fa-credit-card"></i>');
        }

        return $tabs;
    }

    private function notifyUsers()
    {
        $users = User::where(['blocked' => 'no', 'activated' => 1])->get();

        $locales = [];
        foreach (config('selectOptions.common.locales') as $locale)
            $locales[$locale] = file_exists(resource_path("lang/$locale"));

        foreach ($users as $user) {
            $url = '/' . $user->language . '/wallet';
            $locale = empty($locales[$user->language]) ? 'en' : $user->language;

            $notification = new PersonalNotification([
                'title'   => trans('admin/bank_accounts.message.title', [], $locale),
                'message' => trans('admin/bank_accounts.message.body', ['url' => $url], $locale),
                'displayParams' => ['type' => 'bank']
            ]);

            (new PersonalNotificationService($user, $notification))->send();

            unset($notification);
        }
    }
}
