<?php

namespace Admin\Http\Sections;

use Illuminate\Database\Eloquent\Model;

use Admin\ColumnMetas\BaseMetaData;
use Admin\Facades\AdminDisplayTabbedComponent;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;

use App\Models\Table;
use App\Models\StaffSession;
use App\Models\Staff;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class LoopCommandEvents extends BaseSection
{
    const AVAILABLE_ROUND_TYPES = [
        'baccarat_round',
        'roulette_round'
    ];

    public $canCreate = false;
    public $canDelete = false;

    public function initialize()
    {
        $this->updating(function ($config, $model) {
            request()->request->add(['staff_id' => auth()->user()->id]);
        });
    }

    public function setDefaultAlias()
    {
        if (\Request::segment(2) === 'loop_command_events_baccarat' || \Request::segment(2) === 'loop_command_events_roulette') {
            $this->alias = \Request::segment(2);
        } else {
            $this->alias = 'loop_command_events';
        }
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display
            ->paginate(config('selectOptions.common.adminPagination'))
            ->setOrder([5, 'desc']);

        $display->with(['table', 'staffSession', 'loopCommand', 'staff']);

        $display->setApply(function ($query) use($scopes) {
            $type = request()->get('type');

            if ($type) {
                $query->where('roundable_type', $type);
            }

            if (array_key_exists('table_id', $scopes)) {
                $query->where('table_id', $scopes['table_id']);
            }
        });

        $display->setColumns([
            AdminColumn::sText('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('table.id', $model),
            AdminColumn::sRelatedLink('staffSession.id', $model),
            AdminColumn::sRelatedLink('staffSession.staff.name', $model),
            AdminColumn::sRelatedLink('roundable.id', $model),
            AdminColumn::sDateTime('created_at', $model),
            AdminColumn::sRelatedLink('loopCommand.description', $model),
            AdminColumn::sText('status', $model),
            AdminColumn::sRelatedLink('staff.name', $model),
            AdminColumn::sDateTime('updated_at', $model),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sSelect('table.id', $model, Table::class)->setDisplay('slug')->setReadOnly(true),
                    AdminFormElement::sText('roundable.id', $model)->setReadOnly(true),
                    AdminFormElement::sText('staffSession.id', $model)->setReadOnly(true),
                    AdminFormElement::sText('created_at', $model)->setReadOnly(true),

                ],
                [
                    AdminFormElement::sSelect('staff_session_id', $model,  StaffSession::class)
                        ->setDisplay('staff.name')
                        ->setReadOnly(true)
                        ->setLabel(trans('admin/loop_command_events.staffSession->staff->name')),
                    AdminFormElement::sSelect('status', $model)
                        ->setEnum(config('selectOptions.loop_command_events.status'))
                        ->setDefaultValue('not_processed')
                        ->required(),
                    AdminFormElement::sSelect('staff_id', $model, Staff::class)
                        ->setDisplay('name')
                        ->setReadOnly(true)
                        ->setHtmlAttribute('disabled', 'disabled')
                        ->setLabel(trans('admin/loop_command_events.staff->name')),
                    AdminFormElement::sText('updated_at', $model)->setReadOnly(true),
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'cancel' => new Cancel(),
        ]);

        return $form;
    }
}
