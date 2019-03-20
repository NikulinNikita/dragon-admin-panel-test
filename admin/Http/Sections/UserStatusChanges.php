<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\DateTimeMetaData;
use Admin\ColumnMetas\RelationsWithTranslationMetaData;
use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\User;
use App\Models\UserStatus;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class UserStatusChanges extends BaseSection
{
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'user_id')) {
                $query->where("{$model->getTable()}.user_id", array_get($scopes, 'user_id'));
            }
        });
        $display->setParameters(['user_id' => array_get($scopes, 'user_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['userStatus', 'user']);

        $columns = [
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('userStatus.title', $model)->setOrderable(true)->setMetaData(RelationsWithTranslationMetaData::class),
            AdminColumn::sText('status', $model)->setMetaData(BaseMetaData::class),
            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
        ];
        if ( ! $scopes) {
            array_splice($columns, 1, 0, [
                AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true),
            ]);
        } else {
            $this->canCreate = false;
            $display->getColumns()->disableControls();
            $display->setHtmlAttribute('class', 'b-remove_header');
        }

        return $display->setColumns($columns);
    }

    public function onEdit($id)
    {
        $model = $this->getModel();

        $userStatusChanges = AdminForm::panel();
        $userStatusChanges->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sSelect('user_status_id', $model, UserStatus::class)->setDisplay('translations.title')->setValidationRules(['required']),
                    AdminFormElement::sSelect('user_id', $model, User::class)->setDisplay('name')->setValidationRules(['required']),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.user_status_changes.status'))->setValidationRules(['required']),
                ],
                [
                    //
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        return $userStatusChanges;
    }
}
