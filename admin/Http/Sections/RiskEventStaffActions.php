<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\MorphByManyMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\Risk;
use App\Models\RiskLevel;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\Buttons\Cancel;

class RiskEventStaffActions extends BaseSection
{
    public $canCreate = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['riskableUsers', 'riskableStaff', 'riskLevel', 'risk']);

        $display->setColumns([
            
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $form = AdminForm::panel();
        $form->addBody(
            \AdminFormElement::columns([
                [
                    \AdminFormElement::select('assigned_status', $model)->setEnum(config('selectOptions.risk_event_staff_actions.status')),
                    \AdminFormElement::sTextArea('comment', $model),
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'cancel' => new Cancel(),
        ]);

        return $form;
    }
}
