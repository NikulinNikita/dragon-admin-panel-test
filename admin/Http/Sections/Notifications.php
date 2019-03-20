<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\DateTimeMetaData;
use Admin\ColumnMetas\MorphMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\Staff;
use App\Models\User;
use DB;
use Illuminate\Database\Eloquent\Model;
use MessagesStack;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class Notifications extends BaseSection
{
    public $canDelete = false;

    public function initialize()
    {
        $this->creating(function ($config, Model $model) {
            $attributes = request()->except('sleeping_owl_tab_id', '_method', '_redirectBack', '_token', 'next_action');
            $model->setRawAttributes($attributes + $model->getAttributes());

            DB::transaction(function () use ($model) {
                (new \Admin\Services\User\PersonalNotificationService($model->notifiable, new \App\Notifications\User\PersonalNotification(
                    [
                        'title'   => $model->{"data->title"},
                        'message' => $model->{"data->message"},
                        'link'    => $model->{"data->link"},
                        'type'    => $model->{"data->style"}
                    ]
                )))->send();

                MessagesStack::addSuccess('Notification successfully sent!');
            });

            return false;
        });
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatables()->setHtmlAttribute('class', 'table-default table-hover');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (!auth()->user()->hasAnyRole(['superadmin'])) {
                $query->where("notifiable_type", 'staff')->where('notifiable_id', auth()->id());
            }
        });
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[6, 'desc']]);
        $display->with(['notifiable', 'staff']);

//        $staffArr    = Staff::selectRaw("CONCAT('Staff','-',staff.id) AS `k`")->selectRaw("CONCAT('Staff',' - ',staff.name) AS `v`")->pluck('v', 'k')->all();
//        $usersArr    = User::selectRaw("CONCAT('User','-',users.id) AS `k`")->selectRaw("CONCAT('User',' - ',users.name) AS `v`")->pluck('v', 'k')->all();
//        $resultedArr = array_merge($staffArr, $usersArr);
        $display->setColumnFilters([
            AdminColumnFilter::sSelect(Staff::class, 'name')->setColumnName('staff_id')->multiple(),
//            AdminColumnFilter::sSelect(Staff::class, 'name')->setColumnName('staff_id')->multiple()
//                ->setSelectRaws(["custom_id" => "'Staff-', staff.id", "custom_name" => "'Staff - ', staff.name"])
//            ,
            null,
//            AdminColumnFilter::select($resultedArr)->setColumnName('notifiable_id')->multiple(),
            null,
            null,
            null,
            null,
            AdminColumnFilterComponent::rangeDate(),
        ])->setPlacement('table.header');

        $display->setColumns([
//            AdminColumn::link('id', 'uuid')->setWidth('300px'),
            AdminColumn::sRelatedLink('staff.name', $model)->setOrderable(true),
            AdminColumn::sRelatedLink('notifiable.name', $model)->setOrderable(true)->setMetaData(MorphMetaData::class),
            AdminColumn::sText('data->title', $model),
            AdminColumn::sText('data->message', $model),
            AdminColumn::sText('data->link', $model),
            AdminColumn::sText('data->style', $model),
            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model       = $this->getModel();
        $parentModel = $model->whereId($id)->first();

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sSelect('staff_id', $model, Staff::class)->setDisplay('name')->setDefaultValue(auth()->id())->setReadonly(true),
                    AdminFormElement::sSelect('notifiable_type', $model, [false => trans("admin/common.NothingSelected"), 'staff' => 'Staff', 'user' => 'User'])
                                    ->setValidationRules(['required'])->setReadonly($id)->setSortable(false),
                    AdminFormElement::sDepSelect('notifiable_id', $model, ['notifiable_type'], $parentModel)->setDisplay('name')->setDepTarget('model')
                                    ->setValidationRules(['required'])->setReadonly($id),
                ],
                [
                    AdminFormElement::sText('data->title', $model)->setValidationRules(['required']),
                    AdminFormElement::sTextArea('data->message', $model)->setValidationRules(['required']),
                    AdminFormElement::sText('data->link', $model)->setDefaultValue('#')->setValidationRules(['required']),
                    AdminFormElement::sSelect('data->style', $model, ['success' => 'success', 'warning' => 'warning'])->setDefaultValue('success')
                                    ->setValidationRules(['required']),
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
