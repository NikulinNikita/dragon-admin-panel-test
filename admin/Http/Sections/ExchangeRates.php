<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use AdminColumn;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\Currency;
use App\Models\ExchangeRate;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class ExchangeRates extends BaseSection
{
    public $canEdit = false;
    public $canDelete = false;

    public function initialize()
    {
        $this->created(function ($config, Model $model) {
            DB::beginTransaction();
            try {
                ExchangeRate::where('id', '!=', $model->id)->where('currency_id', $model->currency_id)->update(['status' => 'inactive']);
                $currentCurrencyId = session()->get('admin.currency.id');
                if ($model->currency_id == $currentCurrencyId) {
                    session()->put('admin.currency.rate', $model->rate);
                }
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

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[5, 'desc']]);
        $display->with(['staff', 'currency']);

        $buttonType = "fetchCurrencies";
        $display->getActions()->setView(view('admin::datatables.toolbar',
            compact('model', 'requestQuery', 'buttonType')))->setPlacement('panel.heading.actions');

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('rate', $model),
            AdminColumn::sRelatedLink('staff.name', $model)->setOrderable(true),
            AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
            AdminColumn::sText('status', $model)->setMetaData(BaseMetaData::class),
            AdminColumn::sText('created_at', $model)->setWidth('150px'),
        ]);

        $display->getColumns()->disableControls();

        return $display;
    }

    public function onCreate()
    {
        $model = $this->getModel();

        $exchangeRate = AdminForm::panel();
        $exchangeRate->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('staff_id', $model)->setHtmlAttribute('disabled', 'disabled')->mutateValue(function ($value) {
                        return auth()->user()->id;
                    })->setDefaultValue(auth()->user()->name),
                    AdminFormElement::sText('rate', $model)->setValidationRules(['required']),
                ],
                [
                    AdminFormElement::sDateTime('created_at', $model)->setDefaultValue(Carbon::now())->setReadonly(true),
                    AdminFormElement::sSelect('currency_id', $model, Currency::class)->setDisplay('code')->setValidationRules(['required'])
                                    ->setQueryFilters([['code', '!=', 'USD']]),
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
