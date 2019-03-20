<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\TranslationMetaData;
use Admin\Facades\AdminDisplayTabbedComponent;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Models\Currency;
use App\Models\GatewayCurrency;
use DB;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\Save;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\FormElements;

class Gateways extends BaseSection
{
    public $canDelete = false;

    public function initialize()
    {
        $this->created(function ($config, Model $model) {
            DB::beginTransaction();
            try {
                foreach (Currency::all() as $currency) {
                    $gatewayCurrency = GatewayCurrency::create([
                        'gateway_id'  => $model->id,
                        'currency_id' => $currency->id,
                        'min_limit'   => 0,
                        'max_limit'   => 0,
                    ]);
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
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'asc']]);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('title', $model)->setMetaData(TranslationMetaData::class),
            AdminColumn::sText('description', $model)->setMetaData(TranslationMetaData::class),
            AdminColumnEditable::sCheckbox('enabled_for_deposit', $model)->setWidth('200px'),
            AdminColumnEditable::sCheckbox('enabled_for_withdrawal', $model)->setWidth('200px'),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();

        $gateway = AdminForm::panel();
        $gateway->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sCheckbox('enabled_for_deposit', $model),
                    AdminFormElement::sCheckbox('enabled_for_withdrawal', $model),
                    ! is_null($id) ? AdminFormElement::sText('slug', $model)->setReadonly(true) : AdminFormElement::hidden('slug'),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')
                                    ->setValidationRules(['required']),
                ],
                [
                    AdminDisplayTabbedComponent::getTranslations(['title', 'description', 'duration']),

                ]
            ]),
            new  FormElements(['<hr>']),
            ! is_null($id) ? AdminSection::getModel(GatewayCurrency::class)->fireDisplay(['scopes' => ['gateway_id' => $id]]) : ''
        )->getButtons()->setButtons([
            'save'   => ! is_null($id) ? new SaveAndClose() : new Save(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        return $gateway;
    }
}
