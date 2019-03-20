<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\DateTimeMetaData;
use Admin\ColumnMetas\RelationsMetaData;
use Admin\ColumnMetas\RelationsWithTranslationMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminDisplayFilter;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Models\BaccaratBet;
use App\Models\BaccaratCard;
use App\Models\BaccaratResult;
use App\Models\BaccaratRound;
use App\Models\BaccaratShoe;
use App\Models\BaseModel;
use App\Models\Staff;
use App\Models\StaffSession;
use App\Models\Table;
use Carbon\Carbon;
use DB;
use DragonStudio\BaccaratRules\RulesProvider;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\FormElements;

class BaccaratRounds extends BaseSection
{
    public $canCreate = false;
    public $canDelete = false;

    public function initialize()
    {
        $this->updating(function ($config, BaccaratRound $model) {
            $attributes     = request()->except('sleeping_owl_tab_id', '_method', '_redirectBack', '_token', 'next_action',
                'staff_session_id', 'baccarat_shoe_id', 'bet_acception_started_at', 'bet_acception_ended_at', 'ended_at'
            );
            $withoutChanges = true;
            foreach ($attributes as $attributeName => $attributeValue) {
                $withoutChanges = $withoutChanges && $model->{$attributeName} == $attributeValue;
            }
            $model->setRawAttributes($attributes + $model->getAttributes());

            if ( ! $withoutChanges) {
                DB::transaction(function () use ($model) {
                    $playerCards    = [];
                    $playerCardsCol = BaccaratCard::whereIn('id', [$model->player_card_1, $model->player_card_2, $model->player_card_3])->get();
                    foreach ([$model->player_card_1, $model->player_card_2, $model->player_card_3] as $card) {
                        if ($card) {
                            $playerCards[] = $playerCardsCol->where('id', $card)->first();
                        }
                    }
                    $bankerCards    = [];
                    $bankerCardsCol = BaccaratCard::whereIn('id', [$model->banker_card_1, $model->banker_card_2, $model->banker_card_3])->get();
                    foreach ([$model->banker_card_1, $model->banker_card_2, $model->banker_card_3] as $card) {
                        if ($card) {
                            $bankerCards[] = $bankerCardsCol->where('id', $card)->first();
                        }
                    }

                    $gameMechanics    = new RulesProvider();
                    $playerCardsCount = count($playerCards);
                    $bankerCardsCount = count($bankerCards);
                    $playerCardsValue = $gameMechanics->evaluateCardsValue($playerCards);
                    $bankerCardsValue = $gameMechanics->evaluateCardsValue($bankerCards);
                    $roundResultsArr  = $gameMechanics->getRoundResults($playerCards, $bankerCards);
                    $roundResultsIds  = BaccaratResult::whereIn('code', $roundResultsArr)->pluck('id');

                    $model->player_score       = $playerCardsValue;
                    $model->banker_score       = $bankerCardsValue;
                    $model->player_cards_count = $playerCardsCount;
                    $model->banker_cards_count = $bankerCardsCount;
                    $model->status             = 'finished';
                    $model->options            = ['recounted' => true];

                    $model->baccaratResults()->sync($roundResultsIds);
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

            if (array_get($scopes, 'baccarat_shoe_id')) {
                $query->where("{$model->getTable()}.baccarat_shoe_id", array_get($scopes, 'baccarat_shoe_id'));
            }
        });
        $display->setParameters(['staff_session_id' => array_get($scopes, 'staff_session_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['staffSession.staff', 'staffSession.table', 'baccaratResults']);

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

        $lastModel = BaccaratRound::orderBy('id', 'desc')->first();
        if ( ! array_get($scopes, 'noFilters')) {
            if ($lastModel->status === 'failed' && env('APP_MAIN_SERVER', false)) {
                $alias      = $this->getAlias();
                $buttonType = ["restartLoop"];
                $params     = ['type' => 'baccarat', 'id' => $lastModel->id];
                $display->getActions()->setView(view('admin::datatables.toolbar', compact('alias', 'buttonType', 'params')))
                        ->setPlacement('panel.heading.actions');
            }

            $display->setColumnFilters([
                AdminColumnFilter::sSelect(BaccaratRound::class, 'id')->multiple(),
                AdminColumnFilter::sSelect(Staff::class, 'name')->setColumnName('staffSession.staff.name')->multiple()
                                 ->setJoins(['belongsToMany->roles'])->setQueryFilters([['roles.name', 'dealer']]),
                AdminColumnFilter::sSelect(Table::class, 'translations.title')->setColumnName('staffSession.table.title')->multiple(),
                AdminColumnFilter::sSelect(Staff::class, 'name')->setColumnName('baccaratShoe.staff.name')->multiple(),
                AdminColumnFilter::text()->setOperator('contains')->setColumnName('baccaratShoe.id'),
//                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilter::sSelect()->setEnum(config('selectOptions.baccarat_rounds.status'))->multiple(),
                AdminColumnFilterComponent::rangeDate(),
                AdminColumnFilterComponent::rangeDate(),
//                AdminColumnFilterComponent::rangeDate(),
                AdminColumnFilterComponent::rangeDate(),

            ])->setPlacement('table.header');
        }

        $columns = [
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('staffSession.staff.name', $model)->setOrderable(true)->setMetaData(RelationsMetaData::class),
            AdminColumn::sRelatedLink('staffSession.table.title', $model)->setOrderable(true)->setMetaData(RelationsWithTranslationMetaData::class),
            AdminColumn::sText('baccaratShoe.staff.name', $model, 'baccaratShoe.created_at')->setOrderable(true)->setMetaData(RelationsMetaData::class),
            AdminColumn::sRelatedLink('baccaratShoe.id', $model)->setOrderable(true)->setMetaData(RelationsMetaData::class),
            AdminColumn::sText('number', $model),
            AdminColumn::sText('is_extra_results_allowed', $model),
            AdminColumn::sText('status', $model)->setMetaData(BaseMetaData::class),
            AdminColumn::sText('bet_acception_started_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
            AdminColumn::sText('bet_acception_ended_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
//            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
            AdminColumn::sText('ended_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
            AdminColumn::sCustom('Results', $model, function (BaseModel $model) {
                $result = '';
                foreach ($model->baccaratResults as $baccaratResult) {
                    $result = $result . "<li><span class=\"label label-info\">{$baccaratResult->code}</span></li>";
                }

                return $result;
            })->setHtmlAttribute('class', 'custom-list-items')->setWidth('60px')->setShowTags(true),
        ];

        if ( ! array_get($scopes, 'baccarat_shoe_id')) {
            $controlButtons = [
                AdminColumn::custom('Round Buttons', function (BaseModel $model) use ($lastModel) {
//                    $lastModels     = BaccaratRound::orderBy('id', 'desc')->take(2)->pluck('id')->all();
//                    $lastOneModel   = array_slice($lastModels, 0, 1);
                    $result = '';

                    if ($lastModel && $lastModel->id === $model->id && $model->status === 'aborted') {
                        $routeRefundBets = route('admin.rounds.refundBets', ['baccarat', $model->id]);
                        $a               = "<a class='btn btn-xs text-center btn-success b-refundRoundBets b-mrb-10' href='#' data-link='{$routeRefundBets}' title='Refund Bets'>
                        <i class='fa fa-money'></i> Refund Bets</a>";
                        $result          = $result . "<li>{$a}</li>";
                    }
//                    if (in_array($model->id, $lastOneModel)) {
//                        $routeStop = route('admin.rounds.stop', ['baccarat', $model->id]);
//                        $a         = "<a class='btn btn-xs text-center btn-danger b-stopRound b-mrb-10' href='#' data-link='{$routeStop}' title='Stop'>
//                    <i class='fa fa-stop'></i> Stop</a>";
//                        $result    = $result . "<li>{$a}</li>";
//                    }
//                    if (in_array($model->id, $lastModels) && ($model->status === 'aborted')) {
//                        $routeRestart = route('admin.rounds.restart', ['baccarat', $model->id]);
//                        $a            = "<a class='btn btn-xs text-center btn-primary b-restartRound b-mrb-10' href='#' data-link='{$routeRestart}' title='Restart'>
//                        <i class='fa fa-play'></i> Restart</a>";
//                        $result       = $result . "<li>{$a}</li>";
//                    }
//                    if (in_array($model->id, $lastModels) && isset($model->options->recounted) &&
//                        ! (isset($model->options->processedBets) && isset($model->options->restartedLoop))) {
//                        $routeRecount = route('admin.rounds.manipulate', ['baccarat', $model->id]);
//                        $a            = "<a class='btn btn-xs text-center btn-warning b-manipulateRound b-mrb-10' href='#' data-link='{$routeRecount}' title='Recount'>
//                        <i class='fa fa-refresh'></i> Recount</a>";
//                        $result       = $result . "<li>{$a}</li>";
//                    }

                    return "<ul>{$result}</ul>";
                })->setHtmlAttribute('class', 'custom-list-items')->setWidth('60px'),
            ];
            $columns        = array_merge($columns, $controlButtons);
        }

        $display->setColumns($columns);

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $baccaratRound = $model->where('id', $id)->first();

        $cards = [];
        $sides = ['player', 'banker'];

        foreach ($sides as $side) {
            $cards[$side] = [];
            for ($i = 1; $i <= 3; $i++) {
                $prop = $side . 'Card' . $i;
                if ($baccaratRound->$prop) {
                    $cards[$side][] = $baccaratRound->$prop->code;
                }
            }
        }


        $roundPlayerCardsHtml = view('admin::baccarat_rounds.cards', ['cards' => $cards['player']])->render();
        $roundBankerCardsHtml = view('admin::baccarat_rounds.cards', ['cards' => $cards['banker']])->render();

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    new FormElements([$roundPlayerCardsHtml]),
                    AdminFormElement::sSelect('player_card_1', $model, BaccaratCard::class)->setDisplay('title')->setSortable(false)->nullable(),
                    AdminFormElement::sSelect('player_card_2', $model, BaccaratCard::class)->setDisplay('title')->setSortable(false)->nullable(),
                    AdminFormElement::sSelect('player_card_3', $model, BaccaratCard::class)->setDisplay('title')->setSortable(false)->nullable(),
                ],
                [
                    new FormElements([$roundBankerCardsHtml]),
                    AdminFormElement::sSelect('banker_card_1', $model, BaccaratCard::class)->setDisplay('title')->setSortable(false)->nullable(),
                    AdminFormElement::sSelect('banker_card_2', $model, BaccaratCard::class)->setDisplay('title')->setSortable(false)->nullable(),
                    AdminFormElement::sSelect('banker_card_3', $model, BaccaratCard::class)->setDisplay('title')->setSortable(false)->nullable(),
                ],
                [
                    AdminFormElement::sSelect('staff_session_id', trans("admin/{$table}.staff->name"), StaffSession::class)->setDisplay('staff.name')
                                    ->setReadonly(true),
                    AdminFormElement::sSelect('staff_session_id', trans("admin/{$table}.table->title"), StaffSession::class)->setDisplay('table.title')
                                    ->setReadonly(true),
                    AdminFormElement::sSelect('baccarat_shoe_id', trans("admin/{$table}.baccaratShoeStaff->name"), BaccaratShoe::class)
                                    ->setDisplay('staff.name')->setReadonly(true),
                ],
                [
                    AdminFormElement::sDateTime('bet_acception_started_at', $model)->setReadonly(true),
                    AdminFormElement::sDateTime('bet_acception_ended_at', $model)->setReadonly(true),
                    AdminFormElement::sDateTime('ended_at', $model)->setReadonly(true),
                ],
            ]),
            AdminFormElement::columns([
                [
                    AdminFormElement::sMultiSelect('baccaratResults', $model, BaccaratResult::class)->setDisplay('code')
                                    ->setReadonly(true),
                ],
                [
                ],
            ])
        )->getButtons()->setButtons([
//            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        $bets = AdminSection::getModel(BaccaratBet::class)->fireDisplay(['scopes' => ['baccarat_round_id' => $id]]);

        $tabs = AdminDisplay::tabbed();
        $tabs->appendTab($form, trans("admin/{$table}.tabs.SpinInfo"))->setIcon('<i class="fa fa-info"></i>');
        if ( ! is_null($id)) {
            $tabs->appendTab($bets, trans("admin/{$table}.tabs.Bets"))->setIcon('<i class="fa fa-credit-card"></i>');
        }

        return $tabs;
    }
}
