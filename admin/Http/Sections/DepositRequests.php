<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\AdminGetterInputMetaData;
use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\DateTimeMetaData;
use Admin\ColumnMetas\RelationsWithTranslationMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use Admin\Services\User\PersonalNotificationService;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminDisplayFilter;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Events\User\UserTillBalanceChange;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\BankAccountOperation;
use App\Models\BaseModel;
use App\Models\Currency;
use App\Models\DepositRequest;
use App\Models\DepositRequestStatusChange;
use App\Models\Gateway;
use App\Models\Operation;
use App\Models\Staff;
use App\Models\User;
use App\Models\UserBankAccountOperation;
use App\Notifications\User\PersonalNotification;
use Carbon\Carbon;
use DB;
use DragonStudio\BonusProgram\Events\UserDepositApproved;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Section;

class DepositRequests extends BaseSection
{
    public $canCreate = false;
    public $canDelete = false;

    public function initialize()
    {
        $this->updating(function (Section $config, DepositRequest $model) {
            $attributes = request()->except('sleeping_owl_tab_id', '_method', '_redirectBack', '_token', 'next_action', 'AdminBankAccountId', 'staffId');
            request()->get('AdminBankAccountId') ? $attributes['bank_account_id'] = request()->get('AdminBankAccountId') : true;
            $oldModel = clone $model;
            $model->setRawAttributes($attributes + $model->getAttributes());

            $this->checkIf(in_array($oldModel->status, ['canceled_by_user', 'succeed']), "Item already has status {$oldModel->status}!");
            if ($model->bankAccount) {
                $this->checkIf((int)$model->currency_id !== $model->bankAccount->currency_id,
                    "User currency({$model->currency->symbol}) is not the same as Bank Account currency({$model->bankAccount->currency->symbol})!");
            }

            $nullableArr      = ['received_amount', 'total_amount'];
            $nulledAttributes = BaseModel::convertEmptyValuesToNull($model->getAttributes(), $nullableArr, true);
            $model->setRawAttributes($nulledAttributes);

            if ($model->getOriginal('status') !== $model->status) {
                $title = 'Deposit Request';

                if (in_array($model->status, ['approved', 'sent_to_recheck_to_operator'])) {
                    DB::transaction(function () use ($config, $model, $title) {
                        $cashiers = Staff::whereHas('roles', function ($query) use ($title) {
                            $query->whereIn('name', ['general', 'operator', 'accountant']);
                        })->get();

                        $message = $model->status === 'approved' ? "Deposit Request approved!" : "Deposit Request sent to recheck to operators!";
                        foreach ($cashiers as $cashier) {
                            (new PersonalNotificationService($cashier, new PersonalNotification(
                                ['title' => $title, 'message' => $message, 'link' => $config->getEditUrl($model->id)]
                            )))->send();
                        }
                    });
                };

                if (in_array($model->status, ['approved_to_proceed', 'sent_to_recheck_to_manager'])) {
                    DB::transaction(function () use ($config, $model, $title) {
                        $model->depositRequestStatusChanges()->where('status', 'approved_to_proceed')->update(['options' => json_encode(['old' => true])]);

                        $managers = Staff::whereHas('roles', function ($query) use ($title) {
                            $query->whereIn('name', ['general', 'manager', 'accountant']);
                        })->get();

                        $message = $model->status === 'approved_to_proceed' ? "Deposit Request approved to proceed!" :
                            "Deposit Request sent to recheck to managers!";
                        foreach ($managers as $manager) {
                            (new PersonalNotificationService($manager, new PersonalNotification(
                                ['title' => $title, 'message' => $message, 'link' => $config->getEditUrl($model->id)]
                            )))->send();
                        }
                    });
                };

                if ($model->status === 'succeed') {
                    DB::transaction(function () use ($model, $title) {
                        $model->received_default_amount = BaseModel::convertToDefaultCurrency($model->currency_id, $model->received_amount, true);
                        $model->default_amount          = BaseModel::convertToDefaultCurrency($model->currency_id, $model->total_amount, true);
                        $model->save();

                        $userTill           = $model->user->userTills->first();
                        $amount             = $model->received_amount;
                        $newUserTillBalance = $userTill->balance + $amount;
                        $operation          = Operation::saveNewOperationAndUserTillBalance($model, $userTill, $amount, $newUserTillBalance,
                            ['params' => ['staff_id' => auth()->id()]]);

                        list($amount, $balance) = BankAccountOperation::getAmountAndBalance($operation);
                        $bankAccountOperation = BankAccountOperation::makeItem($operation, $amount, $balance);

                        if ($model->user_bank_account_id || request()->get('user_bank_account_id')) {
                            list($amount, $balance) = UserBankAccountOperation::getAmountAndBalance($operation);
                            $userBankAccountOperation = UserBankAccountOperation::makeItem($operation, $amount, $balance);
                        }

                        $userTillBalanceChangeEvent = new UserTillBalanceChange($userTill);
                        broadcast($userTillBalanceChangeEvent);

                        (new PersonalNotificationService($model->user, new PersonalNotification(
                            [
                                'title'         => $title,
                                'message'       => 'Deposit Request succeed!',
                                'link'          => '#',
                                'displayParams' => ['type' => 'bank'],
                                'identifier'    => 'deposit_request_succeed'
                            ]
                        )))->send();

                        event(new UserDepositApproved($model));
                    });
                }

                $depositRequestStatusChange = DepositRequestStatusChange::create([
                    'deposit_request_id' => $model->id,
                    'staff_id'           => auth()->id(),
                    'status'             => $model->status,
                ]);
            }

            return true;
        });
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatables()->setHtmlAttribute('class', 'table-default table-hover b-remove_header b-colored_rows');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'user_id')) {
                $query->where("{$model->getTable()}.user_id", array_get($scopes, 'user_id'));
            }
            if (auth()->user()->hasRole('operator')) {
                $query->whereIn("status", ['new', 'approved', 'sent_to_recheck_to_operator', 'sent_to_recheck_to_manager']);
            }
        });
        $display->setParameters(['user_id' => array_get($scopes, 'user_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[10, 'desc']]);
        $display->with(['user', 'userBank', 'userBankAccount', 'bank', 'bankAccount', 'gateway', 'currency', 'depositRequestStatusChanges.staff']);

        $display->setFilters(
            request()->get('userIds') ?
                AdminDisplayFilter::field('user_id')->setAlias('userIds')->setOperator('in')->setTitle(function ($value) {
                    $result = implode(', ', $value);

                    return "User IDs: [{$result}]";
                }) : AdminDisplayFilter::field('xxx'),
            AdminDisplayFilter::custom('todayRegisteredUsers')->setCallback(function ($query, $value) {
                $query->whereHas('user', function ($q) use ($value) {
                    return $q->forTodayOnly();
                });
            })->setTitle('Today Registered Users: [:value]'),
            AdminDisplayFilter::field('status')->setTitle('Status: [:value]'),
            AdminDisplayFilter::custom('date_from')->setCallback(function ($query, $value) {
                $query->where('created_at', '>=', Carbon::parse($value));
            })->setTitle('Date From: [:value]'),
            AdminDisplayFilter::custom('date_to')->setCallback(function ($query, $value) {
                $query->where('created_at', '<=', Carbon::parse($value)->addDay()->subSecond());
            })->setTitle('Date To: [:value]'),
            request()->get('statuses') ?
                AdminDisplayFilter::field('status')->setAlias('statuses')->setOperator('in')->setTitle(function ($value) {
                    $result = implode(', ', $value);

                    return "Statuses: [{$result}]";
                }) : AdminDisplayFilter::field('xxx')
        );

        if ( ! array_get($scopes, 'noFilters')) {
            $display->setColumnFilters([
                null,
                AdminColumnFilter::sSelect(User::class, 'name')->setColumnName('user_id')->multiple(),
                null,
                AdminColumnFilter::sSelect(Bank::class, 'translations.title')->setColumnName('bank_id')->multiple(),
//                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilter::sSelect(Gateway::class, 'translations.title')->setColumnName('gateway_id')->multiple(),
                AdminColumnFilter::sSelect(Currency::class, 'code')->setColumnName('currency_id')->multiple(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
//            null,
//            null,
                AdminColumnFilter::sSelect()->setEnum(config('selectOptions.deposit_requests.status'))->multiple(),
                AdminColumnFilterComponent::rangeDate(),
            ])->setPlacement('table.header');
        }

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true),
            AdminColumn::sText('full_name', $model),
            AdminColumn::sRelatedLink('bank.title', $model)->setOrderable(true)->setMetaData(RelationsWithTranslationMetaData::class),
//            AdminColumn::sRelatedLink('userBankAccount.number', $model)->setOrderable(true),
            AdminColumn::sRelatedLink('bankAccount.number', $model)->setOrderable(true),
            AdminColumn::sRelatedLink('gateway.title', $model)->setOrderable(true)->setMetaData(RelationsWithTranslationMetaData::class),
            AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
//            AdminColumn::sLink('reference', $model),
//            AdminColumn::sLink('transaction_ref', $model),
            AdminColumn::sText('AdminTotalAmount', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('AdminDefaultAmount', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sCustom('status', $model, function (BaseModel $item) {
                $class = $item->status === 'new' ? 'b-bg_color_light_green' : '';

                return "<div class='text-center {$class}'>{$item->status}</a>";
            })->setShowTags(true)->setOrderable(true)->setMetaData(BaseMetaData::class),
            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
        ]);

//        $totalQuery = $this->getModel()->query();
//        $display->getFilters()->initialize();
//        $display->getFilters()->modifyQuery($totalQuery);
//        $display->setColumnsTotal([
//            '<b>Total:</b>',
//            null,
//            null,
//            null,
//            null,
//            null,
//            null,
//            null,
//            null,
//            '<b>' . BaseModel::exchangeCurrency($totalQuery->sum('default_amount')) . '</b>',
//        ],
//            $display->getColumns()->all()->count()
//        );
//        $display->getColumnsTotal()->setPlacement('table.footer');

        return $display;
    }

    public function onEdit($id)
    {
        $model        = $this->getModel();
        $table        = $model->getTable();
        $parentModel  = $model->whereId($id)->first();
        $user         = auth()->user();
        $ableStatuses = in_array($parentModel->status, ['new', 'approved', 'approved_to_proceed', 'sent_to_recheck_to_manager', 'sent_to_recheck_to_operator']);
        $isNotNew     = $parentModel->status !== 'new';

        switch ($parentModel->status) {
            case 'new':
                $statusesList = ['declined', 'new'];
                if ($user->hasAnyRole(['superadmin', 'general', 'manager'])) {
                    $statusesList = array_merge($statusesList, ['approved']);
                }
                break;
            case 'approved':
                $statusesList = ['declined', 'approved'];
                if ($user->hasAnyRole(['superadmin', 'operator'])) {
                    $statusesList = array_merge($statusesList, ['approved_to_proceed', 'sent_to_recheck_to_manager']);
                }
                break;
            case 'sent_to_recheck_to_operator':
                $statusesList = ['declined', 'sent_to_recheck_to_operator'];
                if ($user->hasAnyRole(['superadmin', 'operator'])) {
                    $statusesList = array_merge($statusesList, ['approved_to_proceed', 'sent_to_recheck_to_manager']);
                }
                break;
            case 'sent_to_recheck_to_manager':
                $statusesList = ['declined', 'sent_to_recheck_to_manager'];
                if ($user->hasAnyRole(['superadmin', 'general', 'manager'])) {
                    $statusesList = array_merge($statusesList, ['approved']);
                }
                break;
            case 'approved_to_proceed':
                $statusesList = ['declined', 'approved_to_proceed', 'succeed', 'sent_to_recheck_to_operator'];
                break;
            default:
                $statusesList = [$parentModel->status];
        }
        if ($user->hasRole('operator')) {
            $statusesList = array_where($statusesList, function ($value, $key) {
                return ! in_array($value, ['declined']);
            });
        }

        $approvedStatus                = $parentModel->depositRequestStatusChanges->where('status', 'approved')->first();
        $approvedToProceedStatus       = $parentModel->depositRequestStatusChanges->where('status', 'approved_to_proceed')->first();
        $approvedToSucceedStatus       = $parentModel->depositRequestStatusChanges->where('status', 'succeed')->first();
        $sentToRecheckToManagerStatus  = $parentModel->depositRequestStatusChanges->where('status', 'sent_to_recheck_to_manager')->first();
        $sentToRecheckToOperatorStatus = $parentModel->depositRequestStatusChanges->where('status', 'sent_to_recheck_to_operator')->first();
        $declinedStatus                = $parentModel->depositRequestStatusChanges->where('status', 'declined')->first();
        $statusesArr                   = [
            'approved'                    => [
                'statusObj'  => $approvedStatus,
                'visibility' => ['new', 'approved', 'approved_to_proceed', 'succeed', 'sent_to_recheck_to_manager', 'sent_to_recheck_to_operator'],
                'header'     => trans("admin/{$table}.ApprovedByStaff"),
            ],
            'approved_to_proceed'         => [
                'statusObj'  => $approvedToProceedStatus,
                'visibility' => ['approved', 'approved_to_proceed', 'succeed', 'sent_to_recheck_to_manager', 'sent_to_recheck_to_operator'],
                'header'     => trans("admin/{$table}.ApprovedToProceedByStaff"),
            ],
            'succeed'                     => [
                'statusObj'  => $approvedToSucceedStatus,
                'visibility' => ['approved_to_proceed', 'succeed'],
                'header'     => trans("admin/{$table}.ApprovedToSucceedByStaff"),
            ],
            'sent_to_recheck_to_manager'  => [
                'statusObj'  => $sentToRecheckToManagerStatus,
                'visibility' => ['sent_to_recheck_to_manager'],
                'header'     => trans("admin/{$table}.SentToRecheckByStaff"),
            ],
            'sent_to_recheck_to_operator' => [
                'statusObj'  => $sentToRecheckToOperatorStatus,
                'visibility' => ['sent_to_recheck_to_operator'],
                'header'     => trans("admin/{$table}.SentToRecheckByStaff"),
            ],
            'declined'                    => [
                'statusObj'  => $declinedStatus,
                'visibility' => ['declined'],
                'header'     => trans("admin/{$table}.DeclinedByStaff"),
            ],
        ];
        $statusesResultedArr           = [];

        foreach ($statusesArr as $statusItem) {
            list($statusObj, $visibility, $header) = array_values($statusItem);
            $result = ! $statusObj ?
                AdminFormElement::text('staffId', $header)->setVisibilityCondition(function ($parentModel) use ($visibility) {
                    return in_array($parentModel->status, $visibility);
                })->setHtmlAttribute('disabled', 'disabled')->mutateValue(function ($value) {
                    return auth()->user()->id;
                })->setDefaultValue(auth()->user()->name)->setReadonly(true) :
                AdminFormElement::text('staffId', $header)->setVisibilityCondition(function ($parentModel) use ($visibility) {
                    return in_array($parentModel->status, $visibility);
                })->setDefaultValue($statusObj->staff->name ?? null)->setReadonly(true);

            $statusesResultedArr = array_merge($statusesResultedArr, [$result]);
        }

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('full_name', $model)->required(),
                    AdminFormElement::sText('sent_amount', $model)->required()->setReadonly(true),
                    AdminFormElement::sText('received_amount', $model)->setReadonly(in_array($parentModel->status, ['approved_to_proceed', 'succeed']))
                                    ->setVisibilityCondition(function ($parentModel) {
                                        return in_array($parentModel->status,
                                            ['approved', 'approved_to_proceed', 'succeed', 'sent_to_recheck_to_operator']);
                                    })
                                    ->setValidationRules([
                                        'required_if:status,approved_to_proceed',
                                        'required_if:status,succeed'
                                    ])->addValidationRule('lte:sent_amount', "Received amount must be lower or equal to Sent Amount"),
                    AdminFormElement::sText('total_amount', $model)->setReadonly(in_array($parentModel->status, ['approved_to_proceed', 'succeed']))
                                    ->setVisibilityCondition(function ($parentModel) {
                                        return in_array($parentModel->status,
                                            ['approved', 'approved_to_proceed', 'succeed', 'sent_to_recheck_to_operator']);
                                    })
                                    ->setValidationRules([
                                        'required_if:status,approved_to_proceed',
                                        'required_if:status,succeed'
                                    ])->addValidationRule('lte:received_amount', "Total amount must be lower or equal to Received  Amount"),
                    AdminFormElement::sSelect('currency_id', $model, Currency::class)->setDisplay('code')->required()->setReadonly(true),
                    AdminFormElement::sText('reference', $model)->setReadonly(true),
                    AdminFormElement::sSelect('user_id', $model, User::class)->setDisplay('name')->required()->setReadonly(true),
//                    AdminFormElement::sSelect('user_bank_id', $model, UserBank::class)->setDisplay('title')->setReadonly(true),
//                    AdminFormElement::sSelect('user_bank_account_id', $model, UserBankAccount::class)->setDisplay('number')->setReadonly(true),
                ],
                array_merge($statusesResultedArr, [
                    AdminFormElement::sSelect('status', $model)->setEnum($statusesList)->required()
                                    ->setReadonly(in_array($parentModel->status, ['declined', 'canceled_by_user', 'succeed'])),
                    AdminFormElement::sSelect('gateway_id', $model, Gateway::class)->setDisplay('translations.title')
                                    ->setQueryFilters([['enabled_for_deposit', true]])->setReadonly(true)->nullable()->setValidationRules(
                            ['required_if:status,approved', 'required_if:status,approved_to_proceed', 'required_if:status,succeed']),
                    AdminFormElement::sDepSelect('bank_account_id', $model, ['gateway_id'], $parentModel)->setDisplay('AdminCurrencyAndBankNumber')
//                        ->setModelForOptions(BankAccount::class)->setDepKey('_bank.gateway_id')
//                        ->setWith(['currency', 'bank', 'bankAccountOperations'])->setLimit(0)
//                        ->setQueryFilters([['bank_accounts.currency_id', $parentModel->currency_id], ['_bank.gateway_id', $parentModel->gateway_id]])
                                    ->setSearch('number')->setModelForOptions(BankAccount::class)->setDepKey('banks.gateway_id')->setDistinct(true)->setCte(true)
                                    ->setJoins(['currencies', 'banks.hasMany->bank_translations', 'hasMany->bank_account_operations'])
                                    ->setQueryFilters([
                                        ['bank_accounts.currency_id', $parentModel->currency_id],
                                        ['bank_accounts.status', 'active'],
                                        ['banks.gateway_id', $parentModel->gateway_id]
                                    ])
                                    ->setSelectRaws([
                                        "currencies.code, ' - ', bank_translations.title, ' - ', bank_accounts.number, ' - ',
                                        IF(FIRST_VALUE(bank_account_operations.balance) OVER(PARTITION BY bank_account_operations.bank_account_id ORDER BY bank_account_operations.id DESC),
                                         FIRST_VALUE(bank_account_operations.balance) OVER(PARTITION BY bank_account_operations.bank_account_id ORDER BY bank_account_operations.id DESC), 0), 
                                         currencies.symbol"
                                    ])
                                    ->setReadonly(in_array($parentModel->status, ['approved_to_proceed', 'succeed']))->nullable()->setUnlocked(true)
                                    ->setValidationRules(['required_if:status,approved_to_proceed', 'required_if:status,succeed']),
                    AdminFormElement::sTextArea('comment', $model),
                ])
            ])
        )->getButtons()->setButtons([
            'save'   => $ableStatuses ? new SaveAndClose() : false,
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        $depositRequestStatusChanges = AdminSection::getModel(DepositRequestStatusChange::class)->fireDisplay(['scopes' => ['deposit_request_id' => $id]]);

        $tabs = AdminDisplay::tabbed();
        $tabs->appendTab($form, trans("admin/{$table}.tabs.DepositRequest"))->setIcon('<i class="fa fa-info"></i>');
        if ( ! is_null($id)) {
            $tabs->appendTab($depositRequestStatusChanges, trans("admin/{$table}.tabs.StatusChanges"))->setIcon('<i class="fa fa-credit-card"></i>');
        }

        return $tabs;
    }
}
