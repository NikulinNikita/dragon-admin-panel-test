<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\RelationsWithTranslationMetaData;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Models\BaccaratResult;
use App\Models\Currency;
use App\Models\Game;
use App\Models\ResultLimitCurrency;
use App\Models\RouletteResult;
use App\Models\TableLimitCurrency;
use App\Rules\MinMaxTitle;
use DB;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\Save;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\FormElements;

class TableLimits extends BaseSection
{
    public $canDelete = false;

    public function initialize()
    {
        $this->created(function ($config, Model $model) {
            DB::beginTransaction();
            try {
                $limits = explode('-', str_replace(' ', '', $model->title));
                foreach (Currency::all() as $currency) {
                    $tableLimitCurrency = TableLimitCurrency::create([
                        'currency_id'    => $currency->id,
                        'table_limit_id' => $model->id,
                        'min_limit'      => $currency->code === 'USD' ? $limits[0] : \BaseModel::convertDefaultCurrency($currency->id, $limits[0]),
                        'max_limit'      => $currency->code === 'USD' ? $limits[1] : \BaseModel::convertDefaultCurrency($currency->id, $limits[1]),
                    ]);

                    foreach ($model->game_id == 1 ? BaccaratResult::all() : RouletteResult::all() as $result) {
                        $resultLimitCurrency = ResultLimitCurrency::create([
                            'table_limit_currency_id' => $tableLimitCurrency->id,
                            'min_limit'               => $tableLimitCurrency->min_limit,
                            'max_limit'               => $tableLimitCurrency->max_limit,
                            'status'                  => 'inactive',
                        ]);

                        $result->resultLimitCurrencies()->save($resultLimitCurrency);
                    }
                }
            } catch (\Exception $e) {
                DB::rollback();
                $model->delete();
                throw $e;
            }
            DB::commit();
        });

        $this->updated(function ($config, Model $model) {
            DB::beginTransaction();
            try {
                $limits        = explode('-', str_replace(' ', '', $model->title));
                $tableLimitUsd = $model->tableLimitCurrencies->filterFix('currency.code', '=', 'USD')->first();
                $tableLimitUsd->update([
                    'min_limit' => array_first($limits) ?: 0,
                    'max_limit' => array_last($limits) ?: 0,
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                $model->delete();
                throw $e;
            }
            DB::commit();
        });
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_pagination');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['game']);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('game.title', $model)->setOrderable(true)->setMetaData(RelationsWithTranslationMetaData::class),
            AdminColumn::sText('title', $model),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model       = $this->getModel();
        $parentModel = $model->whereId($id)->first();

        $tableLimit = AdminForm::panel();
        $tableLimit->addHeader([])->setHtmlAttribute('class', 'b-has_included_table');
        $tableLimit->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sSelect('game_id', $model, Game::class)->setDisplay('translations.title')->required()->setReadOnly($id),
                    AdminFormElement::sText('title', $model)->setValidationRules(['max:191|required']),
                ],
                [
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')
                                    ->setValidationRules(['required']),
                ]
            ]),
            new  FormElements(['<hr>']),
            ! is_null($id) ? AdminSection::getModel(TableLimitCurrency::class)->fireDisplay(['scopes' => ['table_limit_id' => $id]]) : '',
            new  FormElements(['<hr>']),
            ! is_null($id) ? AdminSection::getModel($parentModel->game->id === 1 ? BaccaratResult::class : RouletteResult::class)
                                         ->fireDisplay(['scopes' => ['table_limit_id' => $id]]) : ''
        )->getButtons()->setButtons([
            'save'   => ! is_null($id) ? new SaveAndClose() : new Save(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        return $tableLimit;
    }
}
