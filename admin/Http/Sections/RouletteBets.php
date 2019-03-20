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
use App\Models\RouletteBet;
use App\Models\RouletteResult;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\FormElements;

class RouletteBets extends BaseSection
{
    public $canCreate = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'rouletteBetIds')) {
                $query->whereIn("{$model->getTable()}.id", array_get($scopes, 'rouletteBetIds'));
            }
            if (array_get($scopes, 'user_till_id') || request()->get('user_till_id')) {
                $query->where("{$model->getTable()}.user_till_id", array_get($scopes, 'user_till_id') ?? request()->get('user_till_id'));
            }
            if (array_get($scopes, 'roulette_round_id') || request()->get('roulette_round_id')) {
                $query->where("{$model->getTable()}.roulette_round_id", array_get($scopes, 'roulette_round_id') ?? request()->get('roulette_round_id'));
            }
        });
        $display->setParameters(['user_till_id' => array_get($scopes, 'user_till_id'), 'roulette_round_id' => array_get($scopes, 'roulette_round_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with([
            'userTill.currency',
            'rouletteResultPreset.rouletteResult',
            'rouletteRound.winnerCell',
            'rouletteRound.staffSession.staff',
            'userSession.user'
        ]);

        $display->setFilters(
            AdminDisplayFilter::custom('staff')->setCallback(function ($query, $value) {
                $query->whereHas('rouletteRound.staffSession.staff', function ($q) use ($value) {
                    return $q->where('name', $value);
                });
            })->setTitle('Staff: [:value]'),
            AdminDisplayFilter::custom('staffSessionId')->setCallback(function ($query, $value) {
                $query->whereHas('rouletteRound.staffSession', function ($q) use ($value) {
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
                AdminColumnFilter::sSelect(RouletteBet::class, 'id')->setColumnName('id')->multiple(),
                AdminColumnFilterComponent::rangeDate(),
                AdminColumnFilter::sSelect(Staff::class, 'name')->setColumnName('rouletteRound.staffSession.staff.name')->multiple()
                                 ->setJoins(['belongsToMany->roles'])->setQueryFilters([['roles.name', 'dealer']]),
                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilter::sSelect(User::class, 'name')->setColumnName('userSession.user.name')->multiple(),
                AdminColumnFilter::sSelect(RouletteResult::class, 'code')->setColumnName('rouletteResultPreset.rouletteResult.code')->multiple(),
                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                null,
                AdminColumnFilter::sSelect()->setEnum(config('selectOptions.roulette_bets.status'))->multiple(),
            ])->setPlacement('table.header');
        }

        $columns = [
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
            AdminColumn::sRelatedLink('rouletteRound.staffSession.staff.name', $model)->setOrderable(true)->setMetaData(RelationsMetaData::class),
            AdminColumn::sRelatedLink('rouletteRound.id', $model, \App\Models\RouletteRound::class)->setOrderable(true),
            AdminColumn::sRelatedLink('rouletteResultPreset.rouletteResult.code', $model)->setOrderable(true)->setMetaData(RelationsMetaData::class),
            AdminColumn::sRelatedLink('rouletteRound.winnerCell.value', $model),
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
        $model      = $this->getModel();
        $bet        = $model->findOrFail($id);
        $wonCell    = view('admin::roulette_bets.cells', ['cells' => [$bet->rouletteRound->rouletteCell], 'title' => 'wonCell'])->render();
        $wonPresets = view('admin::roulette_bets.cells', ['cells' => $bet->rouletteResultPreset->rouletteCells, 'title' => 'wonPresets'])->render();

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('roulette_round_id', $model)->setReadOnly(true),
                    AdminFormElement::sText('userTill.id', $model)->setReadOnly(true),
                    AdminFormElement::sText('AdminStaffSessionStaff.name', $model)->setReadOnly(true),
                    AdminFormElement::sText('AdminStaffSessionTable.title', $model)->setReadOnly(true),
                    AdminFormElement::sText('AdminUser.name', $model)->setReadOnly(true),

                ],
                [
                    AdminFormElement::sText('AdminAmount', $model)->setReadOnly(true),
                    AdminFormElement::sText('AdminOutcome', $model)->setReadOnly(true),
                    AdminFormElement::sText('AdminDefaultAmount', $model)->setReadOnly(true),
                    AdminFormElement::sText('AdminDefaultOutcome', $model)->setReadOnly(true),
                ],
                [
                    new FormElements([$wonCell]),
                    AdminFormElement::sText('AdminRouletteResult.code', $model)->setReadOnly(true),
                    new FormElements([$wonPresets]),
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
