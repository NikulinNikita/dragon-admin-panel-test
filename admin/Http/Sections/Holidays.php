<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\TranslationMetaData;
use Admin\Facades\AdminDisplayTabbedComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\Currency;
use App\Models\Holiday;
use App\Models\HolidayBonusAmount;
use App\Models\Region;
use Illuminate\Validation\ValidationException;
use SleepingOwl\Admin\Form\FormElements;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\Save;
use Carbon\Carbon;

class Holidays extends BaseSection
{
    protected static $amounts;


    public function initialize()
    {
        $amountsSaver = function($model) {
            $model->bonusAmounts()->delete();

            foreach (self::$amounts as $currencyId => $amount) {
                if (!(float)$amount) {
                    continue;
                }

                $bonusAmount = new HolidayBonusAmount();

                $bonusAmount->holiday_id = $model->id;
                $bonusAmount->currency_id = $currencyId;
                $bonusAmount->amount = $amount;

                $bonusAmount->save();
            }
        };

        $preProcessing = function($config, Holiday $model) use ($amountsSaver) {
            self::$amounts = request()->post('amount');

            /*$currencies = Currency::all()->keyBy('id');
            foreach (self::$amounts as $currencyId => $amount) {
                if (!(float)$amount) {
                    \MessagesStack::addError(trans('validation.required', [
                        'attribute' => trans('admin/holidays.bonus_amount') . ' - ' . $currencies[$currencyId]->code
                    ]));
                    throw new ValidationException(1);
                }
            }*/

            if ($model->is_recurring) {
                $model->year = null;
            }
        };

        $this->creating($preProcessing);
        $this->updating($preProcessing);

        $postProcessing = function($config, Holiday $model) use ($amountsSaver) {
            if ($model->is_global) {
                $model->regions()->detach();
            }

            $amountsSaver($model);
        };

        $this->created($postProcessing);
        $this->updated($postProcessing);
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync();

        $display->setColumnFilters([
            null,
            AdminColumnFilter::text('number')->setOperator('contains'),
            null, //AdminColumnFilterComponent::rangeDate(),
            null,
            null,
            null, //AdminColumnFilter::select(Region::class, 'title')->setColumnName('id')->multiple(),
        ])->setPlacement('table.header');

        $display
            ->setHtmlAttribute('class', 'table-default table-hover')
            ->paginate(config('selectOptions.common.adminPagination'))
            ->setOrder([0, 'desc'])
            ->with('regions')
            ->setColumns([
                AdminColumn::sLink('id', '#')->setWidth('30px'),
                AdminColumn::sText('title', $model)->setMetaData(TranslationMetaData::class)->setOrderable(true),
                AdminColumn::sText('formatted_date', $model)->setOrderable(false),
                //AdminColumn::sText('description', $model)->setMetaData(TranslationMetaData::class),
                AdminColumn::sCustom('Recurring', $model, function(Holiday $model) {
                    return $model->is_recurring
                        ? '<i class="fa fa-check text-success"></i>'
                        : '';
                })->setShowTags(true),
                AdminColumn::sCustom('Usage Limited', $model, function(Holiday $model) {
                    return $model->is_usage_limited
                        ? '<i class="fa fa-check text-success"></i>'
                        : '';
                })->setShowTags(true),
                AdminColumn::sLists('regions.title', $model),
                /*AdminColumn::custom('Regions', function (Holiday $model) {
                    if ($model->is_global) {
                        return '<span class="label label-success">Global</span>';
                    }

                    $output = '<ul>';
                    foreach ($model->regions as $region)
                        $output.= '<li><span class="label label-info">' . $region->title . '</span></li>';
                    $output.= '</ul>';

                    return $output;
                })->setHtmlAttribute('class', 'custom-list-items')->setShowTags(true),*/
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
                    AdminDisplayTabbedComponent::getTranslations(['title', 'description'])
                ],
            ]),
            AdminFormElement::columns([
                [
                    AdminFormElement::sCheckbox('is_recurring', $model)
                        ->setHtmlAttribute('onclick', "document.getElementById('year').closest('div.form-group').style.display = this.checked ? 'none' : 'block'")
                ],
                [
                    AdminFormElement::sText('day', $model)
                        ->required()
                        ->addValidationRule('integer')
                        ->addValidationRule('between:1,31')
                ],
                [
                    AdminFormElement::sSelect('month', $model, [
                        1 => 'January',
                        2 => 'February',
                        3 => 'March',
                        4 => 'April',
                        5 => 'May',
                        6 => 'June',
                        7 => 'July',
                        8 => 'August',
                        9 => 'September',
                        10 => 'October',
                        11 => 'November',
                        12 => 'December'
                    ])
                        ->required()
                        ->setSortable(false)
                        ->addValidationRule('integer')
                        ->addValidationRule('between:1,12')
                ],
                [
                    AdminFormElement::sText('year', $model)
                        ->setDefaultValue(Carbon::now()->year + 1)
                        ->addValidationRule('required_if:is_recurring,0')
                        ->addValidationRule('integer')
                        ->addValidationRule('between:1990,2090')
                ]
            ]),
            AdminFormElement::columns([
                [
                    AdminFormElement::sSelect('is_global', $model, [0 => 'No', 1 => 'Yes'])
                        ->required()
                        ->setHtmlAttribute('onchange', "document.getElementById('regions').closest('div.form-group').style.display = '0' == this.value ? 'block' : 'none'")
                ],
                [
                    /*AdminFormElement::dependentselect('regions', 'Regions')
                        ->setModelForOptions(Region::class)
                        ->setDisplay('title')
                        ->setDataDepends(['is_global'])
                        ->setLoadOptionsQueryPreparer(function($item, $query) {
                            return $item->getDependValue('is_global')
                                ? app()->make($item->getDependValue('is_global'))
                                : $query->where('id', 0);
                        })
                        ->setReadOnly(1)
                        ->required(),*/
                    AdminFormElement::sMultiSelect('regions', $model, Region::class)
                        //->addValidationRule('required_if:is_global,0')
                        ->setDisplay('title')
                ]
            ]),
            AdminFormElement::columns([
                [
                    AdminFormElement::sSelect('is_usage_limited', $model, [0 => 'No', 1 => 'Yes'])
                        ->required()
                ],
                [
                    AdminFormElement::sNumber('wager', $model)
                        ->addValidationRule('nullable')
                        ->addValidationRule('integer')
                ]
            ]),
            new FormElements(['<hr/>', '<h4>' . trans('admin/holidays.bonus_amounts') . '</h4>']),
            $this->composeAmountInputs($model, $id),
            new FormElements(["<script>var el = document.getElementById('is_global');
if ('object' === typeof el && el.value !== '0') {el.value = '1'; el.click();}</script>"]),
            new FormElements(["<script>var el = document.getElementById('is_recurring');
if ('object' === typeof el && el.checked) {el.checked = false; el.click();}</script>"])
        );

        $form->getButtons()->setButtons([
            'save'   => new Save(),
            'delete' => new Delete(),
            'cancel' => new Cancel()
        ]);

        return $form;
    }

    private function composeAmountInputs($model, $id)
    {
        $currencies = Currency::all();

        $bonusAmounts = $id
            ? Holiday::find($id)->bonusAmounts()->get()->pluck('amount', 'currency_id')
            : [];

        $columns = [];
        foreach ($currencies as $currency) {
            $value = isset(request()->post('amount')[$currency->id])
                ? request()->post('amount')[$currency->id]
                : ($bonusAmounts[$currency->id] ?? '0.00');

            $htmlInput = <<<HTML
<div class="col-md-3">
    <div class="form-elements">
        <div class="form-group form-element-text">
            <label class="control-label">
                %s
                <!-- <span class="form-element-required">*</span>-->
            </label>
            <input class="form-control" type="text" name="%s" value="%s">    
        </div>
    </div>
</div>
HTML;
            $htmlInput = sprintf($htmlInput,
                trans('admin/holidays.amount->' . $currency->code),
                'amount[' . $currency->id . ']',
                $value);

            $columns[] = $htmlInput;

            /*
            $control = AdminFormElement::sText('amount.' . $currency->id, $model)
                ->setDefaultValue('0.00')
                ->addValidationRule('required')
                ->addValidationRule('numeric');

            if (isset($bonusAmounts[$currency->id])) {
                $control->setValue($bonusAmounts[$currency->id]);
            }

            $columns[] = [$control];
            */
        }

        //return AdminFormElement::columns($columns);
        return new FormElements($columns);
    }
}
