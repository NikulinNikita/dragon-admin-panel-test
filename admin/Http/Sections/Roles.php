<?php

namespace Admin\Http\Sections;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\BaseModel;
use App\Models\Permission;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class Roles extends BaseSection
{
    public $canDelete = false;

    public function onDisplay()
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'asc']]);
        $display->with(['permissions']);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('name', $model),
            AdminColumn::sText('display_name', $model),
            AdminColumn::sText('description', $model),
//            AdminColumn::sLists('permissions.display_name', $model)->setWidth('600px'),
            AdminColumn::sCustom('permissions.display_name', $model, function (BaseModel $model) {
                $result = '';
                foreach ($model->permissions->groupBy('category') as $cat => $permissions) {
                    $result = $result . ($cat !== 'All' ? "<ul>{$cat}</ul>" : "");
                    foreach ($permissions as $permission) {
                        $result = $result . "<li><span class=\"label label-info\">{$permission->display_name}</span></li>";
                    }
                }

                return $result;
            })->setHtmlAttribute('class', 'custom-list-items')->setWidth('600px')->setShowTags(true),
//            AdminColumn::select('type', 'Role type')->setEnum(config('selectOptions.roles.type')),
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
                    AdminFormElement::sText('name', $model)->setValidationRules(['min:3|max:150|required|_unique']),
                    AdminFormElement::sText('display_name', $model)->setValidationRules(['min:3|max:150']),
                ],
                [
                    AdminFormElement::sText('description', $model),
                    AdminFormElement::sMultiSelect('permissions', $model, Permission::class)->setDisplay('AdminPermissionNameWithCategory'),
//                    AdminFormElement::sSelect('type', 'Role type')->setEnum(config('selectOptions.roles.type'))->required(),
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
