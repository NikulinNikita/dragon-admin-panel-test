<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\RelationsWithTranslationMetaData;
use Admin\ColumnMetas\TranslationMetaData;
use Admin\Facades\AdminDisplayTabbedComponent;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\BaseModel;
use App\Models\Game;
use App\Models\Table;
use App\Models\TableLimit;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use App\Models\LoopCommandEvent;

class Tables extends BaseSection
{
    public $canDelete = false;

    public function initialize()
    {
        $this->created(function ($config, Table $model) {
            $this->reorder($model);
        });

        $this->updating(function ($config, Table $model) {
            $this->reorder($model);
        });
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->paginate(config('selectOptions.common.adminPagination'));
        $display->with(['game', 'tableLimits']);

        $display->setApply(function ($query) {
            $query->orderBy('order', 'asc');
        });

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('game.title', $model)->setOrderable(true)->setMetaData(RelationsWithTranslationMetaData::class),
            AdminColumn::sText('title', $model)->setMetaData(TranslationMetaData::class),
            AdminColumn::sText('description', $model)->setMetaData(TranslationMetaData::class),
            AdminColumn::sCustom('Table Limits', $model, function (BaseModel $model) {
                $result = '';
                foreach ($model->tableLimits as $tableLimit) {
                    $result = $result . "<li><span class=\"label label-info\">{$tableLimit->title}</span></li>";
                }

                return "<ul style='padding: 0;'>{$result}</ul>";
            })->setHtmlAttribute('class', 'custom-list-items')->setWidth('150px')->setShowTags(true),
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

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sSelect('game_id', $model, Game::class)->setDisplay('translations.title')->required(),
                    AdminFormElement::sMultiSelect('tableLimits', $model, TableLimit::class)->setDisplay('title')
                                    ->setLoadOptionsQueryPreparer(function ($item, $query) {
                                        return $query->join("games", "table_limits.game_id", '=', "games.id")
                                                     ->join("table_limit_currencies", "table_limit_currencies.table_limit_id", '=', "table_limits.id")
                                                     ->where("table_limit_currencies.currency_id", 1)->orderBy("table_limit_currencies.min_limit", 'asc')
                                                     ->selectRaw("table_limits.id AS `id`")->selectRaw("CONCAT(games.slug, ' - ', table_limits.title) AS `title`");
                                    })->setSortable(false),
                    ! is_null($id) ? AdminFormElement::sText('slug', $model)->setReadonly(true) : AdminFormElement::hidden('slug'),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')->required(),
                    AdminFormElement::sText('order', $model)->setDefaultValue(0),
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

        $tabs = AdminDisplay::tabbed();

        $loopCommandEvents = \AdminSection::getModel(LoopCommandEvent::class)->fireDisplay(['scopes' => ['table_id' => $id]]);

        $tabs->appendTab($form, trans('admin/tables.info'));
        $tabs->appendTab($loopCommandEvents, trans('admin/tables.loop_command_events'));

        return $tabs;
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
