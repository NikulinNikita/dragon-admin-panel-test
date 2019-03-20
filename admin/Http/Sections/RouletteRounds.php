<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\DateTimeMetaData;
use Admin\ColumnMetas\RelationsMetaData;
use Admin\ColumnMetas\RelationsValueMetaData;
use Admin\ColumnMetas\RelationsWithTranslationMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminDisplayFilter;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Models\BaseModel;
use App\Models\RouletteBet;
use App\Models\RouletteCell;
use App\Models\RouletteResult;
use App\Models\RouletteRound;
use App\Models\Staff;
use App\Models\StaffSession;
use App\Models\Table;
use Carbon\Carbon;
use DB;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class RouletteRounds extends BaseSection
{
    public $canCreate = false;
    public $canDelete = false;

    public function initialize()
    {
        $this->updating(function ($config, RouletteRound $model) {
            $attributes     = request()->except('sleeping_owl_tab_id', '_method', '_redirectBack', '_token', 'next_action',
                'staff_session_id', 'bet_acception_started_at', 'bet_acception_ended_at', 'ended_at'
            );
            $withoutChanges = true;
            foreach ($attributes as $attributeName => $attributeValue) {
                $withoutChanges = $withoutChanges && $model->{$attributeName} == $attributeValue;
            }
            $model->setRawAttributes($attributes + $model->getAttributes());

            if ( ! $withoutChanges) {
                DB::transaction(function () use ($model) {
                    $model->status  = 'finished';
                    $model->options = ['recounted' => true];
                    $model->rouletteResultPresets()->detach();
                    $newPresets = (new RouletteRound)->getPresetsByCell($model->rouletteCell);

                    foreach ($newPresets as $preset) {
                        $model->winnerPresets()->attach($preset);
                    }
                });
            }
        });
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'staff_session_id')) {
                $query->where("{$model->getTable()}.staff_session_id", array_get($scopes, 'staff_session_id'));
            }
        });
        $display->setParameters(['staff_session_id' => array_get($scopes, 'staff_session_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['staffSession.staff', 'staffSession.table', 'rouletteCell', 'rouletteResultPresets.rouletteResult']);

        $display->setFilters(
            AdminDisplayFilter::custom('staff')->setCallback(function ($query, $value) {
                $query->whereHas('staffSession.staff', function ($q) use ($value) {
                    return $q->where('name', $value);
                });
            })->setTitle('Staff: [:value]'),
            AdminDisplayFilter::custom('table')->setCallback(function ($query, $value) {
                $query->whereHas('staffSession.table', function ($q) use ($value) {
                    return $q->where('slug', $value);
                });
            })->setTitle('Table: [:value]'),
            AdminDisplayFilter::custom('date_from')->setCallback(function ($query, $value) {
                $query->where('created_at', '>=', Carbon::parse($value));
            })->setTitle('Date From: [:value]'),
            AdminDisplayFilter::custom('date_to')->setCallback(function ($query, $value) {
                $query->where('created_at', '<=', request()->get('dateTime') ? Carbon::parse($value) : Carbon::parse($value)->addDay()->subSecond());
            })->setTitle('Date To: [:value]')
        );

        $lastModel = RouletteRound::orderBy('id', 'desc')->first();
        if ( ! array_get($scopes, 'noFilters')) {
            if ($lastModel->status === 'failed' && env('APP_MAIN_SERVER', false)) {
                $alias      = $this->getAlias();
                $buttonType = ["restartLoop"];
                $params     = ['type' => 'roulette', 'id' => $lastModel->id];
                $display->getActions()->setView(view('admin::datatables.toolbar', compact('alias', 'buttonType', 'params')))
                        ->setPlacement('panel.heading.actions');
            }

            $display->setColumnFilters([
                AdminColumnFilter::sSelect(RouletteRound::class, 'id')->multiple(),
                AdminColumnFilter::sSelect(Staff::class, 'name')->setColumnName('staffSession.staff.name')->multiple()
                                 ->setJoins(['belongsToMany->roles'])->setQueryFilters([['roles.name', 'dealer']]),
                AdminColumnFilter::sSelect(Table::class, 'translations.title')->setColumnName('staffSession.table.title')->multiple(),
                AdminColumnFilter::sSelect(RouletteCell::class, 'value')->setColumnName('rouletteCell.value')->multiple(),
                AdminColumnFilter::sSelect()->setEnum(['green', 'red', 'black'])->multiple(),
                AdminColumnFilter::sSelect()->setEnum(config('selectOptions.baccarat_rounds.status'))->multiple(),
                AdminColumnFilterComponent::rangeDate(),
                AdminColumnFilterComponent::rangeDate(),
//                AdminColumnFilterComponent::rangeDate(),
                AdminColumnFilterComponent::rangeDate(),

            ])->setPlacement('table.header');
        }

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('staffSession.staff.name', $model)->setOrderable(true)->setMetaData(RelationsMetaData::class),
            AdminColumn::sRelatedLink('staffSession.table.title', $model)->setOrderable(true)->setMetaData(RelationsWithTranslationMetaData::class),
            AdminColumn::sRelatedLink('rouletteCell.value', $model)->setOrderable(true)->setMetaData(RelationsMetaData::class),
            AdminColumn::sRelatedLink('rouletteCell.color', $model)->setOrderable(true)->setMetaData(RelationsValueMetaData::class),
            AdminColumn::sText('status', $model)->setMetaData(BaseMetaData::class),
            AdminColumn::sText('bet_acception_started_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
            AdminColumn::sText('bet_acception_ended_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
//            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
            AdminColumn::sText('ended_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
            AdminColumn::sCustom('Results', $model, function (BaseModel $model) {
                $codes  = [];
                $result = '';
                foreach ($model->rouletteResultPresets as $winnerPreset) {
                    $codes[$winnerPreset->rouletteResult->code] = $winnerPreset->rouletteResult->code;
                }
                foreach ($codes as $code) {
                    $result = $result . "<li><span class=\"label label-info\">{$code}</span></li>";
                }

                return $result;
            })->setHtmlAttribute('class', 'custom-list-items')->setWidth('60px')->setShowTags(true),
            AdminColumn::custom('Round Buttons', function (BaseModel $model) use ($lastModel) {
//                $lastModels = RouletteRound::orderBy('id', 'desc')->take(2)->pluck('id')->all();
//                $lastOneModel = array_slice($lastModels, 0, 1);
                $result = '';

                if ($lastModel && $lastModel->id === $model->id && $model->status === 'aborted') {
                    $routeRefundBets = route('admin.rounds.refundBets', ['roulette', $model->id]);
                    $a               = "<a class='btn btn-xs text-center btn-success b-refundRoundBets b-mrb-10' href='#' data-link='{$routeRefundBets}' title='Refund Bets'>
                        <i class='fa fa-money'></i> Refund Bets</a>";
                    $result          = $result . "<li>{$a}</li>";
                }
//                $routeStop = route('admin.rounds.stop', ['roulette', $model->id]);
//                if(in_array($model->id, $lastOneModel)) {
//                    $a = "<a class='btn btn-xs text-center btn-danger b-stopRound b-mrb-10' href='#' data-link='{$routeStop}' title='Stop'>
//                    <i class='fa fa-stop'></i> Stop</a>";
//                    $result = $result . "<li>{$a}</li>";
//                }
//                $routeRestart = route('admin.rounds.restart', ['roulette', $model->id]);
//                if(in_array($model->id, $lastModels) && ($model->status === 'aborted')) {
//                    $a = "<a class='btn btn-xs text-center btn-primary b-restartRound b-mrb-10' href='#' data-link='{$routeRestart}' title='Restart'>
//                        <i class='fa fa-play'></i> Restart</a>";
//                    $result = $result . "<li>{$a}</li>";
//                }
//                $routeRecount = route('admin.rounds.manipulate', ['roulette', $model->id]);
//                if(in_array($model->id, $lastModels) && isset($model->options->recounted) &&
//                   ! (isset($model->options->processedBets) && isset($model->options->restartedLoop))) {
//                    $a = "<a class='btn btn-xs text-center btn-warning b-manipulateRound b-mrb-10' href='#' data-link='{$routeRecount}' title='Recount'>
//                        <i class='fa fa-refresh'></i> Recount</a>";
//                    $result = $result . "<li>{$a}</li>";
//                }

                return "<ul>{$result}</ul>";
            })->setHtmlAttribute('class', 'custom-list-items')->setWidth('60px'),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sSelect('roulette_cell_id', $model, RouletteCell::class)->setDisplay('AdminRouletteCellValue')
                                    ->setWith(true)->setLimit(0),
                    AdminFormElement::sSelect('staff_session_id', trans("admin/{$table}.staff->name"), StaffSession::class)->setDisplay('staff.name')
                                    ->setReadonly(true),
                    AdminFormElement::sSelect('staff_session_id', trans("admin/{$table}.table->title"), StaffSession::class)->setDisplay('table.title')
                                    ->setReadonly(true),
                    AdminFormElement::sMultiSelect('AdminRouletteResults', $model, RouletteResult::class)->setDisplay('code')
                                    ->setReadonly(true)->setSortable(false),
                ],
                [
                    AdminFormElement::sDateTime('bet_acception_started_at', $model)->setReadonly(true),
                    AdminFormElement::sDateTime('bet_acception_ended_at', $model)->setReadonly(true),
                    AdminFormElement::sDateTime('ended_at', $model)->setReadonly(true),
                ],
            ])
        )->getButtons()->setButtons([
//            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        $bets = AdminSection::getModel(RouletteBet::class)->fireDisplay(['scopes' => ['roulette_round_id' => $id]]);

        $tabs = AdminDisplay::tabbed();
        $tabs->appendTab($form, trans("admin/{$table}.tabs.SpinInfo"))->setIcon('<i class="fa fa-info"></i>');
        if ( ! is_null($id)) {
            $tabs->appendTab($bets, trans("admin/{$table}.tabs.Bets"))->setIcon('<i class="fa fa-credit-card"></i>');
        }

        return $tabs;
    }
}
