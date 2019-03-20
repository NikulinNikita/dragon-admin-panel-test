<?php

namespace Admin\Http\Sections;

use Illuminate\Database\Eloquent\Model;
use App\Models\Game;
use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\TranslationMetaData;
use Admin\Facades\AdminDisplayTabbedComponent;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class Games extends BaseSection
{
    public $canDelete = false;

    public function getEditTitle()
    {
        $modelNameAndId = class_basename($this->getClass()) . ' ' . \Request::segment(3);
        $defaultTitle   = trans('sleeping_owl::lang.model.edit', ['title' => $this->getTitle()]);

        return "{$defaultTitle} -- {$modelNameAndId}";
    }

    public function initialize()
    {
        $this->created(function ($config, Game $model) {
            $this->reorder($model);
        });

        $this->updating(function ($config, Game $model) {
            $this->reorder($model);
        });
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->paginate(config('selectOptions.common.adminPagination'));

        $display->setApply(function ($query) {
            $query->orderBy('order', 'asc');
        });

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('title', $model)->setMetaData(TranslationMetaData::class),
            AdminColumn::sText('description', $model)->setMetaData(TranslationMetaData::class),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
            AdminColumn::sText('order', $model)
               ->setHtmlAttribute('class', 'text-center')
               ->setWidth('100px'),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    ! is_null($id) ? AdminFormElement::sText('slug', $model)->setReadonly(true) : AdminFormElement::hidden('slug'),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')->required(),
                    AdminFormElement::sText('order', $model),
                ],
                [
                    AdminDisplayTabbedComponent::getTranslations(['title', 'description'], ['title' => ['min:1', 'max:191']]),
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        return $form;
    }

    private function reorder($model)
    {
        $order = request()->get('order');

        if (!is_null($order)) {
            $model->changeOrder((int)$order);
            
            $model->save();
        }
    }
}
