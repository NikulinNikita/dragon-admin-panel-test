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
use App\Models\BankAccount;
use App\Models\BankAccountOperation;
use App\Models\BaseModel;
use App\Models\Currency;
use App\Models\DepositRequest;
use App\Models\Gateway;
use App\Models\Operation;
use App\Models\Staff;
use App\Models\User;
use App\Models\UserBank;
use App\Models\UserBankAccount;
use App\Models\UserBankAccountOperation;
use App\Models\WithdrawalRequest;
use App\Models\WithdrawalRequestStatusChange;
use App\Notifications\User\PersonalNotification;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\FormElements;
use SleepingOwl\Admin\Section;

class WithdrawalRequests extends BaseSection
{
    const OPERATABLE_TYPES = ['baccarat_bet', 'roulette_bet'];

    public $canCreate = false;
    public $canDelete = false;

    public function initialize()
    {
        $this->updating(function (Section $config, Model $model) {
            $attributes = request()->except('sleeping_owl_tab_id', '_method', '_redirectBack', '_token', 'next_action', 'AdminBankAccountId', 'staffId');
            request()->get('AdminBankAccountId') ? $attributes['bank_account_id'] = request()->get('AdminBankAccountId') : true;
            $oldModel = clone $model;
            $model->setRawAttributes($attributes + $model->getAttributes());

            $userTill = $model->user->userTills->first();
            $this->checkIf(in_array($oldModel->status, ['canceled_by_user', 'succeed']), "Item already has status {$oldModel->status}!");
            $this->checkIf(! in_array($model->status, ['canceled_by_user', 'declined']) && $model->received_amount &&
                           $userTill->balance < $model->received_amount,
                "Not enough money on user balance! ({$userTill->balance}{$userTill->currency->symbol})");
            if ($model->bankAccount) {
                $this->checkIf((int)$model->currency_id !== $model->bankAccount->currency_id,
                    "User currency({$model->currency->symbol}) is not the same as Bank Account currency({$model->bankAccount->currency->symbol})!");
                $this->checkIf(! in_array($model->status, ['canceled_by_user', 'declined']) && $model->bank_account_id && $model->received_amount &&
                               $model->bankAccount->BankAccountOperationsBalance < $model->received_amount,
                    "Not enough money on Organization Bank Account balance! ({$model->bankAccount->BankAccountOperationsBalance}{$model->bankAccount->currency->symbol})");
            }

            $nullableArr      = ['total_amount'];
            $nulledAttributes = BaseModel::convertEmptyValuesToNull($model->getAttributes(), $nullableArr, true);
            $model->setRawAttributes($nulledAttributes);

            if ($model->getOriginal('status') !== $model->status) {
                $title = 'Withdrawal Request';

                if (in_array($model->status, ['approved', 'sent_to_recheck_to_operator'])) {
                    DB::transaction(function () use ($config, $model, $title) {
                        $cashiers = Staff::whereHas('roles', function ($query) {
                            $query->whereIn('name', ['general', 'operator', 'accountant']);
                        })->get();

                        $message = $model->status === 'approved' ? "Withdrawal Request approved!" : "Withdrawal Request sent to recheck to operators!";
                        foreach ($cashiers as $cashier) {
                            (new PersonalNotificationService($cashier, new PersonalNotification(
                                ['title' => $title, 'message' => $message, 'link' => $config->getEditUrl($model->id)]
                            )))->send();
                        }
                    });
                };

                if (in_array($model->status, ['approved_to_proceed', 'sent_to_recheck_to_manager'])) {
                    DB::transaction(function () use ($config, $model, $title) {
                        $model->withdrawalRequestStatusChanges()->where('status', 'approved_to_proceed')->update(['options' => json_encode(['old' => true])]);

                        $managers = Staff::whereHas('roles', function ($query) {
                            $query->whereIn('name', ['general', 'manager', 'accountant']);
                        })->get();

                        $message = $model->status === 'approved_to_proceed' ? "Withdrawal Request approved to proceed!" :
                            "Withdrawal Request sent to recheck to managers!";
                        foreach ($managers as $manager) {
                            (new PersonalNotificationService($manager, new PersonalNotification(
                                ['title' => $title, 'message' => $message, 'link' => $config->getEditUrl($model->id)]
                            )))->send();
                        }
                    });
                };

                if ($model->status === 'succeed') {
                    DB::transaction(function () use ($model, $userTill, $title) {
                        $model->received_default_amount = BaseModel::convertToDefaultCurrency($model->currency_id, $model->received_amount, true);
                        $model->default_amount          = BaseModel::convertToDefaultCurrency($model->currency_id, $model->total_amount, true);
                        $model->save();

                        $amount             = -$model->received_amount;
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
                            ['title' => $title, 'message' => 'Withdrawal Request succeed!', 'link' => '#', 'identifier' => 'withdrawal_request_succeed']
                        )))->send();
                    });
                }

                $withdrawalRequestStatusChange = WithdrawalRequestStatusChange::create([
                    'withdrawal_request_id' => $model->id,
                    'staff_id'              => auth()->id(),
                    'status'                => $model->status,
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
                $query->where(function ($q1) {
                    $q1->where('created_at', '>=', Carbon::now()->subDay())->where('created_at', '<=', Carbon::now())->whereIn('status', ['succeed']);
                })->orWhereIn('status', ['new', 'approved', 'sent_to_recheck_to_operator', 'sent_to_recheck_to_manager']);
            }
        });
        $display->setParameters(['user_id' => array_get($scopes, 'user_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[10, 'desc']]);
        $display->with(['user', 'userBank', 'userBankAccount', 'bank', 'bankAccount.bank', 'currency', 'withdrawalRequestStatusChanges']);

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
                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilter::sSelect(Gateway::class, 'translations.title')->setColumnName('gateway_id')->multiple(),
                AdminColumnFilter::sSelect(Currency::class, 'code')->setColumnName('currency_id')->multiple(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
//            null,
//            null,
                AdminColumnFilter::sSelect()->setEnum(config('selectOptions.withdrawal_requests.status'))->multiple(),
                AdminColumnFilterComponent::rangeDate(),
            ])->setPlacement('table.header');
        }

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true),
            AdminColumn::sText('full_name', $model),
            AdminColumn::sRelatedLink('userBankAccount.number', $model)->setOrderable(true),
            AdminColumn::sRelatedLink('bankAccount.number', $model)->setOrderable(true),
            AdminColumn::sRelatedLink('gateway.title', $model)->setOrderable(true)->setMetaData(RelationsWithTranslationMetaData::class),
            AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
//            AdminColumn::sText('reference', $model),
//            AdminColumn::sText('transaction_ref', $model),
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

        $approvedStatus                = $parentModel->withdrawalRequestStatusChanges->where('status', 'approved')->first();
        $approvedToProceedStatus       = $parentModel->withdrawalRequestStatusChanges->where('status', 'approved_to_proceed')->first();
        $approvedToSucceedStatus       = $parentModel->withdrawalRequestStatusChanges->where('status', 'succeed')->first();
        $sentToRecheckToManagerStatus  = $parentModel->withdrawalRequestStatusChanges->where('status', 'sent_to_recheck_to_manager')->first();
        $sentToRecheckToOperatorStatus = $parentModel->withdrawalRequestStatusChanges->where('status', 'sent_to_recheck_to_operator')->first();
        $declinedStatus                = $parentModel->withdrawalRequestStatusChanges->where('status', 'declined')->first();
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
            new FormElements(
                [
                    "<div class='alert bg-danger'>
                        <p><strong>" . trans("admin/{$table}.UserBalance") . ":</strong> {$parentModel->user->MoneyTillBalance}</p>
                    </div>"
                ]
            ),
            AdminFormElement::columns([
                [
                    $parentModel->gateway_id !== 2 ?
                        AdminFormElement::sText('full_name', $model)->required() : '',
                    AdminFormElement::sText('received_amount', $model)->required()->setReadonly(true),
                    AdminFormElement::sText('total_amount', $model)->setReadonly(in_array($parentModel->status, ['approved_to_proceed', 'succeed']))
                                    ->setVisibilityCondition(function ($parentModel) {
                                        return in_array($parentModel->status,
                                            ['approved', 'approved_to_proceed', 'succeed', 'sent_to_recheck_to_operator']);
                                    })
                                    ->setValidationRules([
                                        'required_if:status,approved_to_proceed',
                                        'required_if:status,succeed'
                                    ])->addValidationRule('gte:received_amount', "Total amount must be greater or equal to Requested Amount"),
                    AdminFormElement::sSelect('currency_id', $model, Currency::class)->setDisplay('code')->required()->setReadonly(true),
                    AdminFormElement::sText('reference', $model)->setReadonly(true),
                    AdminFormElement::sSelect('user_id', $model, User::class)->setDisplay('name')->required()->setReadonly(true),
                    $parentModel->gateway_id !== 2 ?
                        AdminFormElement::sSelect('user_bank_id', $model, UserBank::class)->setDisplay('title')->setReadonly(true) : '',
                    $parentModel->gateway_id !== 2 ?
                        AdminFormElement::sSelect('user_bank_account_id', $model, UserBankAccount::class)->setDisplay('number')->setReadonly(true) :
                        AdminFormElement::sText('options->wechat', $model)->setReadonly(true),

                ],
                array_merge($statusesResultedArr, [
                    AdminFormElement::sSelect('status', $model)->setEnum($statusesList)->required()
                                    ->setReadonly(in_array($parentModel->status, ['declined', 'canceled_by_user', 'succeed'])),
                    AdminFormElement::sSelect('gateway_id', $model, Gateway::class)->setDisplay('translations.title')
                                    ->setQueryFilters([['enabled_for_withdrawal', true]])->setReadonly(true)->nullable()->setValidationRules(
                            ['required_if:status,approved', 'required_if:status,approved_to_proceed', 'required_if:status,succeed']),
                    AdminFormElement::sDepSelect('bank_account_id', $model, ['gateway_id'], $parentModel)->setDisplay('AdminCurrencyAndBankNumber')
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

        $lastDeposit = DepositRequest::where(['user_id' => $parentModel->user->id, 'status' => 'succeed'])->orderBy('id', 'desc')->first();

        $withdrawalRequestStatusChanges = AdminSection::getModel(WithdrawalRequestStatusChange::class)->fireDisplay(['scopes' => ['withdrawal_request_id' => $id]]);
        $operationsDisplay              = AdminSection::getModel(Operation::class)
                                                      ->fireDisplay(
                                                          [
                                                              'scopes' =>
                                                                  [
                                                                      'types'              => self::OPERATABLE_TYPES,
                                                                      'last_deposit'       => $lastDeposit->created_at,
                                                                      'request_date'       => $parentModel->created_at,
                                                                      'user_till_id'       => $parentModel->user->moneyTill->id,
                                                                      'withdrawal_request' => true,
                                                                      'noFilters'          => true,
                                                                      'has_opposite_bets'  => false,
                                                                  ]
                                                          ]
                                                      );

        $tabs = AdminDisplay::tabbed();
        $tabs->appendTab($form, trans("admin/{$table}.tabs.WithdrawalRequest"))->setIcon('<i class="fa fa-info"></i>');

        if ( ! is_null($id)) {
            $amountDeposits = DB::table('deposit_requests')->where('status', 'succeed')->where('user_id', $parentModel->user->id)->sum('received_amount');
            $amountBets     =
                DB::table('operations as op')
                  ->leftJoin('baccarat_bets as bb', function ($j) {
                      $j->on('op.operatable_id', '=', 'bb.id')->where('op.operatable_type', '=', 'baccarat_bet')->where('bb.has_opposite_bets', false);
                  })
                  ->leftJoin('roulette_bets as rb', function ($j) {
                      $j->on('op.operatable_id', '=', 'rb.id')->where('op.operatable_type', '=', 'roulette_bet')->where('rb.has_opposite_bets', false);
                  })
                  ->where('op.user_till_id', $parentModel->user->moneyTill->id)
                  ->whereBetween('op.created_at', [$lastDeposit->created_at, $parentModel->created_at])
                  ->where('op.amount', '<', 0)
                  ->whereIn('op.operatable_type', self::OPERATABLE_TYPES)
                  ->selectRaw("SUM(IF(op.operatable_type = 'baccarat_bet', bb.amount, 0)) AS `baccarat_bets_amount`")
                  ->selectRaw("SUM(IF(op.operatable_type = 'roulette_bet', rb.amount, 0))  AS `roulette_bets_amount`")
                  ->groupBy('op.user_till_id')->first();
            $amountBets     = abs(($amountBets->baccarat_bets_amount ?? 0) + ($amountBets->roulette_bets_amount ?? 0));

            $tabs->appendTab($withdrawalRequestStatusChanges, trans("admin/{$table}.tabs.StatusChanges"))->setIcon('<i class="fa fa-credit-card"></i>');
            $tabs->appendTab(new FormElements(
                [
                    "<div class='alert bg-info'>
                        <p><strong>" . trans("admin/{$table}.amount_deposits_request") . ":</strong> {$amountDeposits}</p>
                        <p><strong>" . trans("admin/{$table}.amount_bet") . "</strong> {$amountBets} " . trans("admin/{$table}.amount_bet_hint") . "</p>
                    </div>",
                    $operationsDisplay
                ]
            ),
                trans("admin/{$table}.tabs.Bets")
            )->setIcon('<i class="fa fa-credit-card"></i>');
        }

        return $tabs;
    }
}
