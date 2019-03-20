<?php

namespace Admin\Http\Sections;

use AdminColumn;
use AdminDisplay;
use Illuminate\Database\Eloquent\Model;
use MessagesStack;
use SleepingOwl\Admin\Contracts\Initializable;
use SleepingOwl\Admin\Display\ControlLink;
use SleepingOwl\Admin\Section;
use Illuminate\Validation\ValidationException;

class BaseSection extends Section implements Initializable
{
    public $canCreate = true;
    public $canEdit = true;
    public $canEditViaAsync = true;
    public $canDelete = true;
    protected $checkAccess = true;

    public static function getUrl($model)
    {
        return (new \SleepingOwl\Admin\Navigation\Page($model))->getUrl();
    }

    public function getTitle()
    {
        $table = $this->getModel()->getTable();

        return trans("admin/{$table}.page_title");
    }

    public function initialize()
    {
        return true;
    }

    public function onDisplay()
    {
        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);

        $display->setColumns([
            AdminColumn::link('id', '#')->setWidth('30px'),
        ]);

        return $display;
    }

    public function onCreate()
    {
        return $this->onEdit(null);
    }

    public function onEdit($id)
    {
        //
    }

    public function onDelete($id)
    {
        // todo: remove if unused
    }

    public function isCreatable()
    {
        if ($this->can('create', $this->getModel())) {
            return $this->canCreate || request()->url() === $this->getCreateUrl();
        } else {
            return false;
        }
    }

    public function isEditable(Model $model)
    {
        if ($this->can('edit', $model)) {
            return $this->canEdit || request()->url() === $this->getEditUrl($model->id) || request()->getRequestUri() === "/admin_panel/{$model->getTable()}/async";
        } else {
            return false;
        }
    }

    public function isDeletable(Model $model)
    {
        if ($this->can('edit', $model)) {
            return $this->canDelete || request()->url() === $this->getDeleteUrl($model->id);
        } else {
            return false;
        }
    }

    public function addCustomActionButton(&$display, $action, $icon = null)
    {
        $canAction = "can" . ucwords($action);
        $button    = new ControlLink(function ($model) use ($action) {
            return $this->getDisplayUrl() . '/' . $model->id . '/' . $action . '/';
        }, '', 50);

        $button->hideText();
        $button->setIcon('fa fa-' . ($icon ?? 'pencil'));
        $button->setHtmlAttribute('class', 'btn-primary');

        $display->setHtmlAttribute('class', "b-remove_included_{$action}_button");
        if ($this->{$canAction}) {
            $display->getColumns()->getControlColumn()->addButton($button);
        }
    }

    public function getActionUrl($model, $action)
    {
        return $this->getUrl(get_class($model)) . '/' . $model->id . '/' . $action . '/';
    }

    public function checkIf($conditions, $text)
    {
        if($conditions) {
            $this->throwError($text);
        } else {
            return true;
        }
    }

    public function throwError($text)
    {
        MessagesStack::addError($text);
        throw new ValidationException(1);
    }
}
