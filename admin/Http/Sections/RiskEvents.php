<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\DateTimeMetaData;
use Admin\ColumnMetas\RelationsMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\Risk;
use App\Models\RiskEventStaffAction;
use App\Models\RiskLevel;
use App\Models\User;
use App\Models\Staff;
use SleepingOwl\Admin\Form\FormElements;


class RiskEvents extends BaseSection
{
    public $canCreate = false;
    public $canDelete = false;

    public function initialize()
    {
        $this->updating(function ($config, $model) {
            $result = \DB::transaction(function () use ($model) {
                $riskEventStaffAction = new RiskEventStaffAction();

                $status = request()->request->get('assigned_status');

                $riskEventStaffAction->risk_event_id   = $model->id;
                $riskEventStaffAction->staff_id        = auth()->user()->id;
                $riskEventStaffAction->message         = request()->get('comment');
                $riskEventStaffAction->assigned_status = $status;

                $model->status = $status;

                $riskEventStaffAction->save();
                $model->save();
            });

            if ($result instanceof \Throwable) {
                throw $result;
            }
        });
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'user_id')) {
                $query->whereHas('riskableUsers', function ($q) use ($scopes) {
                    return $q->where('riskables.riskable_id', array_get($scopes, 'user_id'));
                });
            }
            if (array_get($scopes, 'staff_id')) {
                $query->whereHas('riskableStaff', function ($q) use ($scopes) {
                    return $q->where('riskables.riskable_id', array_get($scopes, 'staff_id'));
                });
            }
        });
        $display->setParameters(['user_id' => array_get($scopes, 'user_id')]);
        $display->setParameters(['staff_id' => array_get($scopes, 'staff_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['riskables.riskable', 'riskableUsers', 'riskableStaff', 'riskLevel', 'risk']);

        if ( ! array_get($scopes, 'noFilters')) {
            $display->setColumnFilters(array(
                null,
                AdminColumnFilter::sSelect(array_get($scopes, 'staff_id') ? Staff::class : User::class, 'name')->multiple(),
                AdminColumnFilter::sSelect(RiskLevel::class, 'name')->setColumnName('risk_level_id')->multiple(),
                AdminColumnFilter::sSelect(Risk::class, 'code')->setColumnName('risk_id')->multiple(),
                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilter::sSelect()->setEnum(config('selectOptions.risk_events.status'))->multiple(),
                AdminColumnFilterComponent::rangeDate(),
            ))->setPlacement('table.header');
        }

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sCustom(array_get($scopes, 'staff_id') ? 'riskableStaff.name' : 'riskableUsers.name', $model, function ($instance) use ($model) {
                $result = '';
                foreach ($instance->riskables->whereIn('riskable_type', ['user', 'staff']) as $item) {
                    $result = $result . "<li><span class=\"label label-info\">{$item->riskable->name}</span></li>";
                }

                return "<ul style='padding: 0;'>{$result}</ul>";
            })->setHtmlAttribute('class', 'custom-list-items')->setWidth('150px')->setShowTags(true)->setMetaData(RelationsMetaData::class),
            AdminColumn::sCustom('Level', $model, function ($instance) use ($model) {
                $color = lcfirst($instance->riskLevel->name);

                return "<span class='bg-{$color} btn' style='padding: 3px;'>{$instance->riskLevel->name}</span>";
            })->setShowTags(true),
            AdminColumn::sRelatedLink('risk.code', $model)->setOrderable(true),
            AdminColumn::sText('risk.description', $model)->setOrderable(true)->setLabel(trans('admin/risk_events.riskLevel->description')),
            AdminColumn::sText('status', $model)->setMetaData(BaseMetaData::class),
            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model    = $this->getModel();
        $table    = $model->getTable();
        $instance = \App\Models\RiskEvent::findOrFail($id);

        $tabs = AdminDisplay::tabbed();

        $riskables = \DB::table('riskables')
                        ->join('risk_events', 'riskables.risk_event_id', '=', 'risk_events.id')
                        ->where('riskables.risk_event_id', $id)
                        ->orderBy('riskables.id')
                        ->get();

        $riskablesHtml = view('admin::risk.riskables', ['riskables' => $riskables])->render();

        $form = AdminForm::panel();
        $form->addBody(
            [
                AdminFormElement::columns([
                    [
                        AdminFormElement::sSelect('risk_level_id', $model, RiskLevel::class)->setDisplay('name')->required()->setReadonly(true),
                        AdminFormElement::sSelect('risk_id', $model, Risk::class)->setDisplay('code')->required()->setReadonly(true),
                    ],
                    [
                        AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.risk_events.status'))->required()->setReadonly(true),
                        AdminFormElement::sText('created_at', $model)->required()->setReadonly(true),
                    ]
                ]),

                AdminFormElement::columns([
                    [
                        AdminFormElement::sSelect('risk_id', $model, Risk::class)->setDisplay('description')->required()->setReadonly(true),
                    ],
                ]),

                AdminFormElement::columns([
                    [
                        new FormElements(
                            [
                                $riskablesHtml
                            ]
                        )
                    ],
                ]),

                AdminFormElement::columns([
                    [
                        new FormElements(
                            [
                                '<pre style="max-height: 300px;">' . json_encode($instance->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>'
                            ]
                        )
                    ],
                ]),
            ]
        )->getButtons()->setButtons([]);

        $riskActionsForm       = AdminForm::panel();
        $riskActionsForm->type = 'user_game_limit';

        $riskActionsFormHtml = view('admin::risk.action_form', [
            'risk_event_id'  => $id,
            'current_status' => $instance->status
        ])->render();

        $comments = \DB::table('risk_event_staff_actions')
                       ->join('staff', 'staff.id', '=', 'risk_event_staff_actions.staff_id')
                       ->where('risk_event_staff_actions.risk_event_id', $id)
                       ->orderBy('risk_event_staff_actions.id', 'desc')
                       ->get();

        $commentsHtml = view('admin::risk.comments', ['comments' => $comments]);

        $riskActionsForm->addBody([
            AdminFormElement::columns([
                [
                    new FormElements(
                        [
                            $riskActionsFormHtml
                        ]
                    )
                ],
            ]),
            AdminFormElement::columns([
                [
                    new FormElements(
                        [
                            $commentsHtml
                        ]
                    )
                ],
            ]),
        ]);

        $tabs->appendTab($form, trans("admin/{$table}.tabs.MainInfo"))->setIcon('<i class="fa fa-info"></i>');
        $tabs->appendTab($riskActionsForm, trans("admin/{$table}.tabs.RiskActionsForm"))->setIcon('<i class="fa fa-credit-card"></i>');

        return $tabs;
    }
}
