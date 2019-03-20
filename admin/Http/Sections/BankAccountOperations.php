<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\AdminGetterInputMetaData;
use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\DateTimeMetaData;
use Admin\ColumnMetas\MorphMetaData;
use Admin\ColumnMetas\RelationsWithTranslationMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminDisplayFilter;
use AdminSection;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\BankAccountOperation;
use App\Models\Currency;
use App\Models\DepositRequestStatusChange;
use App\Models\InternalOperation;
use Carbon\Carbon;

class BankAccountOperations extends BaseSection
{
    public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();
        $table = $model->getTable();

        if (auth()->user()->hasRole('operator')) {
            $this->canCreate = false;
        }

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'bank_account_id')) {
                $query->where("{$model->getTable()}.bank_account_id", array_get($scopes, 'bank_account_id'));
            }
//            if (auth()->user()->hasRole('operator')) {
//                $query->where(function ($q1) {
//                    $q1->whereHas('morphDepositRequests.depositRequestStatusChanges', function ($q2) {
//                        $q2->where('staff_id', auth()->user()->id);
//                    })->orWhereHas('morphWithdrawalRequests.withdrawalRequestStatusChanges', function ($q2) {
//                        $q2->where('staff_id', auth()->user()->id);
//                    });
//
//                    return $q1;
//                });
//            }
        });
        $display->setParameters(['bank_account_id' => array_get($scopes, 'bank_account_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['bankAccount.bank', 'operatable', 'currency']);

        $display->setFilters(
            AdminDisplayFilter::custom('number')->setCallback(function ($query, $value) {
                $query->whereHas('bankAccount', function ($q) use ($value) {
                    return $q->where('number', $value);
                });
            })->setTitle('Number: [:value]'),
            AdminDisplayFilter::custom('operatableTypes')->setCallback(function ($query, $value) {
                $query->where(function ($q) use ($value) {
                    foreach ($value as $k => $v) {
                        $sign = strpos($v, '-') !== false ? '<' : '>';
                        $v    = trim($v, '-');
                        if ($k === 0) {
                            $q->where('operatable_type', $v);
                        } else {
                            $q->orWhere('operatable_type', $v);
                        }
                        $q->where('value', $sign, 0);
                    }

                    return $q;
                });
            })->setTitle(function ($value) {
                $result = implode(', ', $value);

                return "Operatable Types: [{$result}]";
            }),
            AdminDisplayFilter::custom('statusChangerStaffId')->setCallback(function ($query, $value) {
//                $query->where(function ($q1) {
//                    $q1->whereHas('morphDepositRequests.depositRequestStatusChanges', function ($q2) {
//                        $q2->where('staff_id', auth()->user()->id);
//                    })->orWhereHas('morphWithdrawalRequests.withdrawalRequestStatusChanges', function ($q2) {
//                        $q2->where('staff_id', auth()->user()->id);
//                    });
//
//                    return $q1;
//                });
            })->setTitle('Status Changer Staff Id: [:value]'),
            AdminDisplayFilter::custom('date_from')->setCallback(function ($query, $value) {
                $query->where('created_at', '>=', Carbon::parse($value));
            })->setTitle('Date From: [:value]'),
            AdminDisplayFilter::custom('date_to')->setCallback(function ($query, $value) {
                $query->where('created_at', '<=', Carbon::parse($value)->addDay()->subSecond());
            })->setTitle('Date To: [:value]')
        );

        $alias      = $this->getAlias();
        $buttonType = ["export"];
        $display->getActions()->setView(view('admin::datatables.toolbar', compact('alias', 'buttonType')))->setPlacement('panel.heading.actions');

        $columnFilters = [
            null,
            AdminColumnFilter::sSelect(Bank::class, 'translations.title')->setColumnName('bankAccount.bank.title')->multiple(),
            AdminColumnFilter::sSelect(Currency::class, 'code')->setColumnName('currency_id')->multiple(),
            AdminColumnFilterComponent::rangeInput(),
            AdminColumnFilterComponent::rangeInput(),
            null,
            null,
            AdminColumnFilter::sSelect()->setEnum(config('selectOptions.bank_account_operations.operatable_type'))->multiple(),
            null,
            AdminColumnFilterComponent::rangeDate(),
        ];
        if ( ! $scopes) {
            array_splice($columnFilters, 2, 0, [
                AdminColumnFilter::sSelect(BankAccount::class, 'number')->setColumnName('bank_account_id')->multiple(),
            ]);
        }
        $display->setColumnFilters($columnFilters)->setPlacement('table.header');

        $exportReport = request()->get('includeHiddenColumns') || strpos(request()->url(), 'exportReport');
        $columns      = [
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('bankAccount.bank.title', $model)->setOrderable(true)->setMetaData(RelationsWithTranslationMetaData::class),
            AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
            AdminColumn::sText('AdminValue', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('AdminBalance', $model)->setMetaData(AdminGetterInputMetaData::class),
            $exportReport ? AdminColumn::sText('AdminDefaultValue', $model)->setOrderable(false) :
                AdminColumn::sText('AdminDefaultValueFormatted', $model)->setOrderable(false),
            $exportReport ? AdminColumn::sText('AdminDefaultBalance', $model)->setOrderable(false) :
                AdminColumn::sText('AdminDefaultBalanceFormatted', $model)->setOrderable(false),
            AdminColumn::sText('AdminOperatableType', $model)->setOrderable(true)->setMetaData(BaseMetaData::class),
            AdminColumn::sRelatedLink('operatable.id', $model)->setOrderable(true)->setMetaData(MorphMetaData::class),
            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
        ];
        if ( ! $scopes) {
            array_splice($columns, 2, 0, [
                AdminColumn::sRelatedLink('bankAccount.number', $model)->setOrderable(true),
            ]);
        } else {
//            $display->getColumns()->disableControls();
        }
        $display->setNewEntryButtonText(trans("admin/{$table}.NewEntryButtonText"));

        if ($exportReport) {
            $display->setColumnsTotal([
                '<b>' . trans("admin/common.Total") . ':</b>',
                null,
                null,
                null,
                null,
                '<b>' . '-' . '</b>',
                null,
                '<b>' . '-' . '</b>',
            ],
                $display->getColumns()->all()->count()
            );
            $display->getColumnsTotal()->setPlacement('table.footer');
        }

        return $display->setColumns($columns);
    }

    public function onCreate()
    {
        return AdminSection::getModel(InternalOperation::class)->fireCreate();
    }
}
