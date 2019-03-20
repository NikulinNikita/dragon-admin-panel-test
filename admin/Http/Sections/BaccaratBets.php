<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\AdminGetterInputMetaData;
use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\DateTimeMetaData;
use Admin\ColumnMetas\RelationsMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminDisplayFilter;
use AdminForm;
use AdminFormElement;
use App\Models\BaccaratBet;
use App\Models\BaccaratResult;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;

class BaccaratBets extends BaseSection
{
    public $canCreate = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'baccaratBetIds')) {
                $query->whereIn("{$model->getTable()}.id", array_get($scopes, 'baccaratBetIds'));
            }
            if (array_get($scopes, 'user_till_id') || request()->get('user_till_id')) {
                $query->where("{$model->getTable()}.user_till_id", array_get($scopes, 'user_till_id') ?? request()->get('user_till_id'));
            }
            if (array_get($scopes, 'baccarat_round_id') || request()->get('baccarat_round_id')) {
                $query->where("{$model->getTable()}.baccarat_round_id", array_get($scopes, 'baccarat_round_id') ?? request()->get('baccarat_round_id'));
            }
        });
        $display->setParameters(['user_till_id' => array_get($scopes, 'user_till_id'), 'baccarat_round_id' => array_get($scopes, 'baccarat_round_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['userTill.currency', 'baccaratResult', 'baccaratRound.staffSession.staff', 'userSession.user']);

        $display->setFilters(
            AdminDisplayFilter::custom('staff')->setCallback(function ($query, $value) {
                $query->whereHas('baccaratRound.staffSession.staff', function ($q) use ($value) {
                    return $q->where('name', $value);
                });
            })->setTitle('Staff: [:value]'),
            AdminDisplayFilter::custom('staffSessionId')->setCallback(function ($query, $value) {
                $query->whereHas('baccaratRound.staffSession', function ($q) use ($value) {
                    return $q->where('id', $value);
                });
            })->setTitle('Staff Session ID: [:value]'),
            AdminDisplayFilter::custom('date_from')->setCallback(function ($query, $value) {
                $query->where('created_at', '>=', Carbon::parse($value));
            })->setTitle('Date From: [:value]'),
            AdminDisplayFilter::custom('date_to')->setCallback(function ($query, $value) {
                $query->where('created_at', '<=', request()->get('dateTime') ? Carbon::parse($value) : Carbon::parse($value)->addDay()->subSecond());
            })->setTitle('Date To: [:value]'),
            request()->get('statuses') ?
                AdminDisplayFilter::field('status')->setAlias('statuses')->setOperator('in')->setTitle(function ($value) {
                    $result = implode(', ', $value);

                    return "Statuses: [{$result}]";
                }) : AdminDisplayFilter::field('xxx')
        );

        if ( ! array_get($scopes, 'noFilters')) {
            $display->setColumnFilters([
                AdminColumnFilter::sSelect(BaccaratBet::class, 'id')->setColumnName('id')->multiple(),
                AdminColumnFilterComponent::rangeDate(),
                AdminColumnFilter::sSelect(Staff::class, 'name')->setColumnName('baccaratRound.staffSession.staff.name')->multiple()
                                 ->setJoins(['belongsToMany->roles'])->setQueryFilters([['roles.name', 'dealer']]),
                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilter::sSelect(User::class, 'name')->setColumnName('userSession.user.name')->multiple(),
                AdminColumnFilter::sSelect(BaccaratResult::class, 'code')->setColumnName('baccarat_result_id')->multiple(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                null,
                AdminColumnFilter::sSelect()->setEnum(config('selectOptions.baccarat_bets.status'))->multiple(),
            ])->setPlacement('table.header');
        }

        $columns = [
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
            AdminColumn::sRelatedLink('baccaratRound.staffSession.staff.name', $model)->setOrderable(true)->setMetaData(RelationsMetaData::class),
            AdminColumn::sRelatedLink('baccaratRound.id', trans('admin/baccarat_bets.baccarat_round_id'))->setOrderable(true),
            AdminColumn::sRelatedLink('baccaratResult.code', $model)->setOrderable(true),
            AdminColumn::sText('AdminAmount', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('AdminOutcome', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('AdminDefaultAmount', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('AdminDefaultOutcome', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('AdminBetBankAmount', $model)->setOrderable(false),
            AdminColumn::sText('status', $model)->setMetaData(BaseMetaData::class),
        ];
        if ( ! array_get($scopes, 'noParentId')) {
            array_splice($columns, 4, 0, [
                AdminColumn::sRelatedLink('userSession.user.name', $model)->setOrderable(true)->setMetaData(RelationsMetaData::class),
            ]);
        }
        $display->setColumns($columns);

        $this->addCustomActionButton($display, 'edit', 'eye');

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('baccarat_round_id', $model)->setReadOnly(true),
                    AdminFormElement::sText('userTill.id', $model)->setReadOnly(true),
                    AdminFormElement::sText('AdminStaffSessionStaff.name', $model)->setReadOnly(true),
                    AdminFormElement::sText('AdminStaffSessionTable.title', $model)->setReadOnly(true),
                    AdminFormElement::sText('userSession.subtable', $model)->setReadOnly(true),
                    AdminFormElement::sText('AdminUser.name', $model)->setReadOnly(true),
                ],
                [
                    AdminFormElement::sText('AdminAmount', $model)->setReadOnly(true),
                    AdminFormElement::sText('AdminOutcome', $model)->setReadOnly(true),
                    AdminFormElement::sText('AdminDefaultAmount', $model)->setReadOnly(true),
                    AdminFormElement::sText('AdminDefaultOutcome', $model)->setReadOnly(true),
                    AdminFormElement::sText('baccaratResult.code', $model)->setReadOnly(true),
                    AdminFormElement::sMultiSelect('baccaratRound.baccaratResults', $model, BaccaratResult::class)->setDisplay('code')->setReadonly(true),
                ],
                [
                    AdminFormElement::sText('status', $model)->setReadOnly(true),
                    AdminFormElement::sText('created_at', $model)->setReadOnly(true),
                    AdminFormElement::sText('processed_at', $model)->setReadOnly(true),
                ]
            ])
        )->getButtons()->setButtons([
//            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        return $form;
    }
}
