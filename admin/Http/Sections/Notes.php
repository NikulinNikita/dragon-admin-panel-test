<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\DateTimeMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class Notes extends BaseSection
{
    public $canDelete = false;

    public function setDefaultAlias()
    {
        if (\Request::segment(2) === 'notes_staff' || \Request::segment(2) === 'notes_user') {
            $this->alias = \Request::segment(2);
        } else {
            $this->alias = 'notes';
        }
    }

    public function onDisplay($scopes = [])
    {
        $model      = $this->getModel();
        $notable_id = array_get($scopes, 'notable_id') ?? (request()->get('notable_id') ?? false);
        $type       = array_get($scopes, 'type') ?? (request()->get('type') ?? str_replace('notes_', '', request()->segment(2)));
        $typeModel  = $type === 'user' ? User::class : Staff::class;

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->setApply(function ($query) use ($notable_id, $type, $typeModel, $model) {
            if ($notable_id) {
                $query->where("{$model->getTable()}.notable_id", $notable_id);
            }
            if ($type) {
                $query->where("{$model->getTable()}.notable_type", $typeModel);
            }
        });
        $display->setParameters(['notable_id' => $notable_id, 'type' => $type]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['notable', 'staff']);

        if ( ! array_get($scopes, 'noFilters')) {
            $columnFilters = [
                null,
                AdminColumnFilter::sSelect(Staff::class, 'name')->setColumnName('staff_id')->multiple(),
                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilterComponent::rangeDate(),
            ];
            if ( ! $scopes) {
                array_splice($columnFilters, 2, 0, [
                    AdminColumnFilter::sSelect($typeModel, 'name')->setColumnName('notable_id')->multiple(),
                ]);
            }
            $display->setColumnFilters($columnFilters)->setPlacement('table.header');
        }

        $columns = [
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('staff.name', $model),
            AdminColumn::sText('text', $model),
            AdminColumn::sText('date', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
        ];
        if ( ! $scopes) {
            array_splice($columns, 2, 0, [
                AdminColumn::sRelatedLink('notable.name', $model),
            ]);
        }

        return $display->setColumns($columns);
    }

    public function onEdit($id)
    {
        $model       = $this->getModel();
        $parentModel = $this->model->whereId($id)->first();
        $type        = request()->get('type') ?: ($parentModel ? strtolower(class_basename($parentModel->notable_type)) : null);
        $type        = $type ?? str_replace('notes_', '', request()->segment(2));
        $notable_id  = request()->get('notable_id');

        $isUserType = $type === 'user';

        if ($isUserType) {
            $notableIdField = AdminFormElement::sSelect('notable_id', [$model, $type], User::class)
                                              ->setDisplay('name')
                                              ->setValidationRules(['required']);
        } else {
            $notableIdField = AdminFormElement::sSelect('notable_id', [$model, $type], Staff::class)
                                              ->setDisplay('name')
                                              ->setValidationRules(['required']);
        }

        if ($id || $notable_id) {
            $notableIdField->setReadonly(true);
        }

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sDateTime('date', $model)->setDefaultValue(Carbon::now())->setValidationRules(['required'])
                                    ->setPickerFormat(config('selectOptions.common.dateTime'))->setFormat(config('selectOptions.common.dateTime')),
                    AdminFormElement::hidden('staff_id')->setDefaultValue(1),
                    AdminFormElement::hidden('notable_type')->setDefaultValue($type === 'user' ? User::class : Staff::class)->setValidationRules(['required']),
                    $notableIdField,
                ],
                [
                    AdminFormElement::sTextArea('text', $model)->setValidationRules(['required']),
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        return $form;
    }
}
