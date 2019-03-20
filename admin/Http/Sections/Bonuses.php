<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\TranslationMetaData;
use Admin\Facades\AdminDisplayTabbedComponent;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use MessagesStack;
use App\Models\BaseModel;
use App\Models\Bonus;
use App\Models\BonusLimit;
use App\Models\Currency;
use App\Models\Game;
use App\Models\UserStatus;
use DB;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\FormElements;
use Illuminate\Validation\ValidationException;

class Bonuses extends BaseSection
{
    public $canCreate = false;
    public $canDelete = false;

    protected $limitationsMap = [
        'limitation_by_number_of_applies'           => 'number_of_applies',
        'limitation_by_game_type'                   => 'game_types',
        'limitation_by_player_status'               => 'player_statuses',
        'limitation_by_identity_approval'           => 'identity_approved_only',
        'limitation_by_documents_submission_period' => 'documents_submission_period',
        'limitation_by_game_duration'               => 'game_duration',
        'limitation_by_min_deposit_amount'          => 'min_deposit_amount',
        'limitation_by_max_bonus_amount'            => 'max_bonus_amount',
        'limitation_by_wagering_period'             => 'wagering_period',
        'limitation_by_wagering_game_type'          => 'wagering_game_types',
        'limitation_by_bonus_usage'                 => 'limitation_by_bonus_usage'
    ];

    protected $relationsFields = ['min_deposit_amount', 'max_bonus_amount'];


    public function initialize()
    {
        $this->updating(function ($config, $model) {
            $attributes = request()->except('sleeping_owl_tab_id', '_method', '_redirectBack', '_token', 'next_action',
                'title:en', 'title:ru', 'title:zh', 'title:zh-CN');

            $model->setRawAttributes($attributes + $model->getAttributes());


            // @TODO refactor
            foreach ($this->relationsFields as $fieldName) {
                session()->push('bonuses.relationship.' . $fieldName, request($fieldName));
            }
            //

            $arrayLikeFields = ['game_types', 'player_statuses', 'wagering_game_types'];

            foreach ($this->limitationsMap as $limitationName => $correspondingKey) {
                if (in_array($correspondingKey, $arrayLikeFields)) {
                    $model->{$correspondingKey} = request($correspondingKey);

                    if ( ! empty($model->{$correspondingKey})) {
                        $model->{$correspondingKey} = implode(',', request($correspondingKey));
                        request()->request->set($correspondingKey, $model->{$correspondingKey});
                    } else {
                        $model->{$limitationName} = 0;
                        request()->request->set($limitationName, 0);
                    }
                }

                if ( ! is_null($correspondingKey) && null === request($limitationName) /*! $model->{$limitationName}*/) {
                    $model->{$limitationName} = 0;
                    request()->request->set($limitationName, 0);

                    $model->{$correspondingKey} = null;
                    request()->request->set($correspondingKey, null);
                }

                if (in_array($correspondingKey, $this->relationsFields)) {
                    unset($model->{$correspondingKey});
                }
            }

            if (!request('wager')) {
                request()->request->set('wager', null);
            }

            foreach ($this->relationsFields as $fieldName) {
                $correspondingKey = array_search($fieldName, $this->limitationsMap);

                if (false === $correspondingKey) {
                    continue;
                }

                if (request($correspondingKey)) {
                    $errors = false;

                    foreach (request($fieldName) as $currencyId => $value) {
                        if (!$value) {
                            if (!$errors) {
                                $errors = true;
                            }

                            MessagesStack::addError(__('validation.required', [
                                'attribute' => trans('admin/bonuses.tabs.Limitations')
                                    . ': '
                                    . trans('admin/bonuses.limitation_by_' . $fieldName)
                                    . ' (' . Currency::find($currencyId)->title . ')'
                            ]));

                            break;
                        }
                    }

                    if ($errors) {
                        throw new ValidationException(1);
                    }
                }
            }

            unset($model->bonus_amount_percent, $model->default_amount_amount);
        });

        $this->updated(function ($config, $model) {
            DB::transaction(function () use ($model) {
                foreach ($this->relationsFields as $fieldName) {
                    $type = str_replace('_amount', '', $fieldName);

                    if ( ! $model->{array_search($fieldName, $this->limitationsMap)}
                         || ! request()->post($fieldName)) {
                        BonusLimit::where(['type' => $type, 'bonus_id' => $model->id])->delete();

                        continue;
                    }

                    foreach (request()->post($fieldName) as $currencyId => $value) {
                        $record = BonusLimit::firstOrNew([
                            'type'        => $type,
                            'bonus_id'    => $model->id,
                            'currency_id' => $currencyId
                        ]);

                        $record->value = $value;

                        $record->save();
                    }
                }

                if (request()->has('default_amount_amount')) {
                    foreach (request()->post('default_amount_amount') as $currencyId => $value) {
                        $value = (float)$value;

                        $record = BonusLimit::where([
                            'type' => 'default_amount',
                            'bonus_id' => $model->id,
                            'currency_id' => $currencyId
                        ])->first();

                        if (!$value) {
                            if ($record) {
                                $record->delete();
                            }

                            continue;
                        }

                        if (!$record) {
                            $record = new BonusLimit();

                            $record->type = 'default_amount';
                            $record->bonus_id = $model->id;
                            $record->currency_id = $currencyId;
                        }

                        $record->value = $value;
                        $record->save();
                    }
                }
            });
        });
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'asc']]);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('title', $model)->setMetaData(TranslationMetaData::class),
            AdminColumn::sText('name', $model),
            AdminColumnEditable::sSelect('target_till', $model)->setEnum(config('selectOptions.bonuses.target_till')),
            AdminColumn::sText('wager', $model)
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();
        $table = $model->getTable();
        $bonus = Bonus::find($id);

        $generalTab = AdminForm::panel();
        $generalTab->setHtmlAttribute('class', 'b-has_included_table');
        $generalTab->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('name', $model)->required()->unique()->addValidationRule('max:45'),
                    AdminFormElement::sSelect('target_till', $model)->required()->setEnum(config('selectOptions.bonuses.target_till')),
                    AdminFormElement::sText('wager', $model)->addValidationRule('integer')
                ],
                [
                    AdminDisplayTabbedComponent::getTranslations(['title'])
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel()
        ]);

        if (isset($bonus->bonus_amount_percent)) {
            $generalTab->addBody(
                new FormElements(['<h4>' . trans("admin/{$table}.Options") . '</h4>']),
                AdminFormElement::columns([[
                    AdminFormElement::sText('bonus_amount_percent', $model)->required()->addValidationRule('integer')
                ], []])
            );
        }


        $gameOptions = Game::all()->pluck('title', 'slug')->toArray();

        $elementIds = '#' . implode(', #', array_keys($this->limitationsMap));

        $limitationsTab = AdminForm::panel();
        $limitationsTab->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sCheckbox('limitation_by_number_of_applies', $model)
                                    ->addValidationRule('')
                                    ->setHtmlAttribute('onclick',
                                        "document.getElementById('number_of_applies').closest('div.form-group').style.display = this.checked ? 'block' : 'none'")
                ],
                [
                    AdminFormElement::sText('number_of_applies', $model)
                                    ->addValidationRule('required_if:limitation_by_number_of_applies,1')
                                    ->addValidationRule('numeric')
                ],
                []
            ]),

            AdminFormElement::columns([
                [
                    AdminFormElement::sCheckbox('limitation_by_game_type', $model)
                                    ->setHtmlAttribute('onclick',
                                        "document.getElementById('game_types').closest('div.form-group').style.display = this.checked ? 'block' : 'none'")
                ],
                [
                    AdminFormElement::sMultiSelect('game_types', $model)->setOptions($gameOptions)
                                    ->addValidationRule('required_if:limitation_by_game_type,1')
                ],
                []
            ]),

            AdminFormElement::columns([
                [
                    AdminFormElement::sCheckbox('limitation_by_player_status', $model)
                                    ->setHtmlAttribute('onclick',
                                        "document.getElementById('player_statuses').closest('div.form-group').style.display = this.checked ? 'block' : 'none'")
                ],
                [new FormElements([$this->getPlayerStatusesHtml($bonus)])],
            ]),

            AdminFormElement::columns([
                [
                    AdminFormElement::sCheckbox('limitation_by_identity_approval', $model)
                                    ->setHtmlAttribute('onclick',
                                        "document.getElementById('identity_approved_only').closest('div.form-group').style.display = this.checked ? 'block' : 'none'")
                ],
                [AdminFormElement::sCheckbox('identity_approved_only', $model)],
                []
            ]),

            AdminFormElement::columns([
                [
                    AdminFormElement::sCheckbox('limitation_by_documents_submission_period', $model)
                                    ->setHtmlAttribute('onclick',
                                        "document.getElementById('documents_submission_period').closest('div.form-group').style.display = this.checked ? 'block' : 'none'")
                ],
                [
                    AdminFormElement::sText('documents_submission_period', $model)
                                    ->addValidationRule('required_if:limitation_by_documents_submission_period,1')
                                    ->addValidationRule('numeric')
                ],
                []
            ]),

            AdminFormElement::columns([
                [
                    AdminFormElement::sCheckbox('limitation_by_game_duration', $model)
                                    ->setHtmlAttribute('onclick',
                                        "document.getElementById('game_duration').closest('div.form-group').style.display = this.checked ? 'block' : 'none'")
                ],
                [
                    AdminFormElement::sText('game_duration', $model)
                                    ->addValidationRule('required_if:limitation_by_game_duration,1')
                                    ->addValidationRule('numeric')
                ],
                []
            ]),

            AdminFormElement::columns([
                [
                    AdminFormElement::sCheckbox('limitation_by_min_deposit_amount', $model)
                                    ->setHtmlAttribute('onclick',
                                        "document.getElementById('min_deposit').closest('div.form-group').style.display = this.checked ? 'block' : 'none'")
                ],
                [new FormElements([$this->getMinDepositAmountHtml($bonus)])],
                []
            ]),

            AdminFormElement::columns([
                [
                    AdminFormElement::sCheckbox('limitation_by_max_bonus_amount', $model)
                                    ->setHtmlAttribute('onclick',
                                        "document.getElementById('max_bonus').closest('div.form-group').style.display = this.checked ? 'block' : 'none'")
                ],
                [new FormElements([$this->getMaxBonusAmountHtml($bonus)])],
                []
            ]),

            AdminFormElement::columns([
                [
                    AdminFormElement::sCheckbox('limitation_by_wagering_period', $model)
                                    ->setHtmlAttribute('onclick',
                                        "document.getElementById('wagering_period').closest('div.form-group').style.display = this.checked ? 'block' : 'none'")
                ],
                [
                    AdminFormElement::sText('wagering_period', $model)
                                    ->addValidationRule('required_if:limitation_by_wagering_period,1')
                                    ->addValidationRule('numeric')
                ],
                []
            ]),

            AdminFormElement::columns([
                [
                    AdminFormElement::sCheckbox('limitation_by_wagering_game_type', $model)
                                    ->setHtmlAttribute('onclick',
                                        "document.getElementById('wagering_game_types').closest('div.form-group').style.display = this.checked ? 'block' : 'none'")
                ],
                [
                    AdminFormElement::sMultiSelect('wagering_game_types', $model)->setOptions($gameOptions)
                                    ->addValidationRule('required_if:limitation_by_wagering_game_type,1')
                ],
                []
            ]),

            AdminFormElement::columns([
                [AdminFormElement::sCheckbox('limitation_by_bonus_usage', $model)],
                [],
                []
            ]),

            new FormElements([
                "<script>var el = document.querySelectorAll('{$elementIds}');
for (var i in el) if ('object' === typeof el[i] && !el[i].checked) {el[i].checked = true; el[i].click();}</script>"
            ])
        );


        $tabs = AdminDisplay::tabbed();
        $tabs->appendTab($generalTab, trans("admin/{$table}.tabs.MainInfo"))->setIcon('<i class="fa fa-info"></i>');
        $tabs->appendTab($limitationsTab, trans("admin/{$table}.tabs.Limitations"))->setIcon('<i class="fa fa-asterisk"></i>');

        if ('birthday_bonus' == $bonus->name) {
            $defaultAmountsTab = AdminForm::panel();
            $defaultAmountsTab->addBody([
                new FormElements(['<div class="col-md-3">' . $this->getDefaultAmountHtml($bonus) . '</div>'])
            ]);

            $tabs->appendTab($defaultAmountsTab, trans("admin/{$table}.tabs.DefaultAmounts"))->setIcon('<i class="fa fa-money"></i>');
        }

        return $tabs;
    }

    private function getPlayerStatusesHtml($bonus)
    {
        $output = '<div class="form-group text-left">'
                  . '<div class="text-center" id="player_statuses">';

        $playerStatuses = UserStatus::all();

        $htmlWidthClass = 'col-md-' . floor(12 / ($playerStatuses->count() + 1));
        $selectedValues = explode(',', $bonus->player_statuses);

        $output .=
            '<div class="">'
            . '<label>&lt;' . __('no status') . '&gt;<br>'
            . '<input type="checkbox" name="player_statuses[]" value="0" ' . (in_array('0', $selectedValues) ? ' checked' : '') . '>'
            . '</label>'
            . '</div>';

        foreach ($playerStatuses as $playerStatus) {
            $html = '<label>' . $playerStatus->title . '<br>'
                    . '<input type="checkbox" name="player_statuses[]" value="' . $playerStatus->id . '" ' . (in_array($playerStatus->id,
                    $selectedValues) ? ' checked' : '') . '>'
                    . '</label>';

            $output .= sprintf('<div class="%s">%s</div>', $htmlWidthClass, $html);
        }

        $output .= '</div></div>';

        return $output;
    }

    private function getMinDepositAmountHtml($bonus)
    {
        return $this->getCurrencyLimitsHtml('min_deposit', $bonus);
    }

    private function getMaxBonusAmountHtml($bonus)
    {
        return $this->getCurrencyLimitsHtml('max_bonus', $bonus);
    }

    private function getDefaultAmountHtml($bonus)
    {
        return $this->getCurrencyLimitsHtml('default_amount', $bonus);
    }

    private function getCurrencyLimitsHtml($name, $bonus)
    {
        $currencies = Currency::all();

        // @TODO refactor
        $requestValue = session('bonuses.relationship.' . $name . '_amount');
        session()->remove('bonuses.relationship.' . $name . '_amount');
        $requestValue = isset($requestValue[0]) ? $requestValue[0] : [];

        $dbValue = BonusLimit::where(['type' => $name, 'bonus_id' => $bonus->id])
            ->get()
            ->pluck('value', 'currency_id')
            ->toArray();

        $output = '<div class="form-group text-left">'
            . '<div id="' . $name . '">';

        foreach ($currencies as $currency) {
            $value = $requestValue[$currency->id] ?? ($dbValue[$currency->id] ?? '');

            $output .=
                '<div>'
                . '<label class="control-label">' . $currency->title . '</label><br>'
                . '<input class="form-control" type="text" name="' . $name . '_amount[' . $currency->id . ']" value="' . htmlspecialchars($value) . '">'
                . '</div>';
        }

        $output .= '</div></div>';

        return $output;
    }
}
