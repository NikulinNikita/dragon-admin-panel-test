<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Models\Note;
use App\Models\RiskEvent;
use App\Models\Role;
use App\Models\Staff as StaffModel;
use App\Models\StaffSession;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class Staff extends BaseSection
{
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (auth()->user()->hasRole('manager')) {
                $query->whereHas('roles', function ($query) {
                    return $query->where('name', 'dealer');
                });
            }
        });
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['roles', 'notes']);

        $display->setColumns([
            AdminColumn::link('id', '#')->setWidth('30px'),
//            AdminColumn::image('AdminPhoto', 'Avatar')->setOrderable(false),
            AdminColumn::sText('name', $model),
            //AdminColumn::sEmail('email', $model),
            AdminColumn::sText('phone', $model),
            AdminColumn::sText('first_name', $model),
            AdminColumn::sText('last_name', $model),
            AdminColumn::sLists('roles.display_name', $model),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model       = $this->getModel();
        $table       = $model->getTable();
        $parentModel = $model->whereId($id)->first();
        $dealerRole  = Role::whereName('dealer')->first();
        $ifDealer    = ($parentModel && ! $parentModel->hasRole('dealer')) ||
                       (request()->get('roles') && ! in_array($dealerRole->id, request()->get('roles'))) ? 'required' : '';

        $commonInformation = AdminForm::panel();
        $commonInformation->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('name', $model)->setValidationRules(['min:3|max:150|required|_unique']),
                    ! $id || ! $parentModel->hasRole('dealer') ?
                        AdminFormElement::sPassword('AdminPassword', $model)->setValidationRules(['min:6|max:150'])->addValidationRule($ifDealer) : '',
                    ! $id || ! $parentModel->hasRole('dealer') ?
                        AdminFormElement::sText('AdminEmail', $model)->setValidationRules(['max:150|email'])->addValidationRule($ifDealer) : '',
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')
                                    ->setValidationRules(['required']),
                    AdminFormElement::sMultiSelect('roles', $model, Role::class)->setDisplay('display_name')->setValidationRules(['required'])
                                    ->exclude(! auth()->user()->hasRole('superadmin') ? [1] : false),
                ],
                [
                    AdminFormElement::sImage('AdminPhoto', $model),
                    AdminFormElement::sText('first_name', $model)->setValidationRules(['max:150']),
                    AdminFormElement::sText('last_name', $model)->setValidationRules(['max:150']),
                    AdminFormElement::sText('address', $model)->setValidationRules(['max:225']),
                    AdminFormElement::sText('phone', $model)->setValidationRules(['max:25']),
                    AdminFormElement::sText('tag_uid', $model),
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        $tabs = AdminDisplay::tabbed();
        $tabs->appendTab($commonInformation, trans("admin/{$table}.tabs.CommonInfo"))->setIcon('<i class="fa fa-info"></i>');

        if ( ! is_null($id)) {
            $instance = StaffModel::findOrFail($id);

            $notes         = AdminSection::getModel(Note::class)->fireDisplay(['scopes' => ['notable_id' => $id, 'type' => 'staff']]);
            $staffSessions = AdminSection::getModel(StaffSession::class)->fireDisplay(['scopes' => ['staff_id' => $id]]);
            $riskEvents    = AdminSection::getModel(RiskEvent::class)->fireDisplay(['scopes' => ['staff_id' => $id]]);

            $tabs->appendTab($notes, trans("admin/{$table}.tabs.Notes"))->setIcon('<i class="fa fa-credit-card"></i>');
            if ($instance->hasRole('dealer')) {
                $tabs->appendTab($staffSessions, trans("admin/{$table}.tabs.StaffSessions"))->setIcon('<i class="fa fa-credit-card"></i>');
                $tabs->appendTab($riskEvents, trans("admin/{$table}.tabs.RiskEvents"))->setIcon('<i class="fa fa-credit-card"></i>');
            }
        }

        return $tabs;
    }
}
