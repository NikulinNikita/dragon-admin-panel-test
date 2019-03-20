<?php

namespace Admin\Http\Sections;

use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class Permissions extends BaseSection
{
    public $canCreate = false;
    public $canDelete = false;

    public function onDisplay()
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'asc']]);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('name', $model),
            AdminColumn::sText('display_name', $model),
            AdminColumn::sText('description', $model),
            AdminColumn::sText('category', $model),
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
                    AdminFormElement::sText('name', $model)->setValidationRules(['min:3|max:191|required|_unique']),
                    AdminFormElement::sText('display_name', $model)->setValidationRules(['min:3|max:191']),
                ],
                [
                    AdminFormElement::sTextArea('description', $model),
                ]
            ])
        )->getButtons()->setButtons([
            'save' => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        return $form;
    }
}
