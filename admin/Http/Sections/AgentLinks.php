<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use AdminColumn;
use AdminColumnEditable;
use AdminColumnFilter;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\User;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class AgentLinks extends BaseSection
{
    public $canCreate = false;
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
        $display->with(['user']);

        if ( ! $scopes) {
            $columnFilters = [
                null,
                null,
                AdminColumnFilter::sSelect(User::class, 'name')->setColumnName('registered_user_id')->multiple(),
                AdminColumnFilter::sSelect()->setEnum(config('selectOptions.agent_links.status'))->multiple(),
            ];
            if ( ! $scopes) {
                array_splice($columnFilters, 4, 0, [
                    AdminColumnFilter::sSelect(User::class, 'name')->setColumnName('user_id')->multiple(),
                ]);
            }
            $display->setColumnFilters($columnFilters)->setPlacement('table.header');
        }

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumnEditable::sText('title', $model),
            AdminColumn::sRelatedLink('registeredUser.name', $model)->setOrderable(true),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.agent_links.status'))->setMetaData(BaseMetaData::class),
        ]);

        if ( ! $scopes) {
            $display->setColumns([
                AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true),
            ]);
        } else {
            $this->canCreate = false;
            $display->getColumns()->disableControls();
            $display->paginate(9999);
            $display->setHtmlAttribute('class', 'b-remove_header');
        }

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();

        $exchangeRate = AdminForm::panel();
        $exchangeRate->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sSelect('user_id', $model, User::class)->setDisplay('name'),
                    AdminFormElement::sText('title', $model)->setValidationRules(['max:50|required']),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.agent_links.status'))->setDefaultValue('unused')
                                    ->setValidationRules(['required']),
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

        return $exchangeRate;
    }
}
