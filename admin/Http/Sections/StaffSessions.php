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
use App\Models\StaffSession;
use App\Models\Table;
use App\Models\Staff;
use AdminForm;
use AdminFormElement;

class StaffSessions extends BaseSection
{
    public $canCreate = false;
    //public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'staff_id')) {
                $query->where("{$model->getTable()}.staff_id", array_get($scopes, 'staff_id'));
            }
        });
        $display->setParameters(['staff_id' => array_get($scopes, 'staff_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['staff', 'table']);

        $columnFilters = [
            AdminColumnFilter::sSelect(StaffSession::class, 'id')->setColumnName('id')->multiple(),
            AdminColumnFilter::sSelect(Table::class, 'translations.title')->setColumnName('table.title')->multiple(),
            AdminColumnFilterComponent::rangeDate('dateTime'),
            AdminColumnFilterComponent::rangeDate('dateTime'),
            AdminColumnFilter::sSelect()->setEnum(config('selectOptions.common.status'))->multiple(),
        ];
        if ( ! $scopes) {
            array_splice($columnFilters, 1, 0, [
                AdminColumnFilter::sSelect(Staff::class, 'name')->setColumnName('staff.name')->multiple(),
            ]);
        }
        $display->setColumnFilters($columnFilters)->setPlacement('table.header');

        $columns = [
            AdminColumn::sText('AdminId', '#')->setWidth('30px')->setShowTags(true)->setMetaData(BaseMetaData::class),
            AdminColumn::sRelatedLink('table.title', $model)->setOrderable(true)->setMetaData(RelationsWithTranslationMetaData::class),
            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
            AdminColumn::sText('ended_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
            AdminColumn::sText('status', $model)->setWidth('150px')->setMetaData(BaseMetaData::class),
        ];

        if ( ! $scopes) {
            array_splice($columns, 1, 0, [
                AdminColumn::sRelatedLink('staff.name', $model)->setOrderable(true)->setMetaData(RelationsMetaData::class),
                ]);
        } else {
            $this->canNotCreate = true;
        }

        $this->canEdit = false;

        $this->addCustomActionButton($display, 'edit', 'eye');

        return $display->setColumns($columns);
    }

    public function onEdit($id)
    {
        $model = $this->getModel();

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('staff_id', $model)->setReadOnly(true),
                    AdminFormElement::sText('staff.name', $model)->setReadOnly(true),
                    AdminFormElement::sSelect('table.id', $model, \App\Models\Table::class)->setDisplay('slug')->setReadOnly(true),

                ],
                [
                    AdminFormElement::sText('status', $model)->setReadOnly(true),
                    AdminFormElement::sText('created_at', $model)->setReadOnly(true),
                    AdminFormElement::sText('ended_at', $model)->setReadOnly(true),
                ]
            ])
        )->getButtons()->setButtons([]);

        return $form;
    }
}
