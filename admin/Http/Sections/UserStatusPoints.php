<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\RelationsWithTranslationMetaData;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\User;
use App\Models\UserStatus;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class UserStatusPoints extends BaseSection
{
    public $canCreate = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['userStatus', 'user']);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true),
            AdminColumn::sText('points', $model),
            AdminColumn::sRelatedLink('userStatus.title', $model)->setOrderable(true)->setMetaData(RelationsWithTranslationMetaData::class),
            AdminColumn::sText('activation_date', $model),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();

        $userStatusPoints = AdminForm::panel();
        $userStatusPoints->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sSelect('user_status_id', $model, UserStatus::class)->setDisplay('translations.title')
                                    ->setValidationRules(['required|unique_with:user_status_points,user_id']),
                    AdminFormElement::sSelect('user_id', $model, User::class)->setDisplay('name')->setValidationRules(['required|_unique']),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')
                                    ->setValidationRules(['required']),
                ],
                [
                    AdminFormElement::sText('points', $model),
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        return $userStatusPoints;
    }
}
