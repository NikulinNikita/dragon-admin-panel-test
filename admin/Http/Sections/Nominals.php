<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Models\BaseModel;
use App\Models\Currency;
use App\Models\NominalValue;
use DB;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\Save;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\FormElements;

class Nominals extends BaseSection
{
    public $canDelete = false;

    public function initialize()
    {
        $this->created(function ($config, Model $model) {
            DB::beginTransaction();
            try {
                foreach (Currency::all() as $currency) {
                    $nominalValue = NominalValue::create([
                        'currency_id' => $currency->id,
                        'nominal_id'  => $model->id,
                        'value'       => 0,
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

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_pagination');
        $display->paginate(9999)->setOrder([[0, 'asc']]);
        $display->with(['nominalValues.currency']);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('color', $model),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
            AdminColumn::sCustom('Nominal Values', $model, function (BaseModel $model) {
                $result = '';
                foreach ($model->nominalValues as $nominalValue) {
                    $result = $result . "<li><span class=\"label label-info\">{$nominalValue->currency->code} - {$nominalValue->value}</span></li>";
                }

                return "<ul style='padding: 0;'>{$result}</ul>";
            })->setHtmlAttribute('class', 'custom-list-items')->setWidth('150px')->setShowTags(true),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();

        $nominal = AdminForm::panel();
        $nominal->addHeader([])->setHtmlAttribute('class', 'b-has_included_table');
        $nominal->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('color', $model)->setValidationRules(['max:191|required']),
                ],
                [
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')
                                    ->setValidationRules(['required']),
                ]
            ]),
            new  FormElements(['<hr>']),
            ! is_null($id) ? AdminSection::getModel(NominalValue::class)->fireDisplay(['scopes' => ['nominal_id' => $id]]) : ''
        )->getButtons()->setButtons([
            'save'   => ! is_null($id) ? new SaveAndClose() : new Save(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        return $nominal;
    }
}
