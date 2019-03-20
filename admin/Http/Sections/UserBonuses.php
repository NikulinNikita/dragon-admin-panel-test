<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\AdminGetterInputMetaData;
use Admin\ColumnMetas\DateTimeMetaData;
use Admin\ColumnMetas\RelationsWithTranslationMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnEditable;
use AdminColumnFilter;
use AdminDisplay;
use AdminDisplayFilter;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Models\BaseModel;
use App\Models\Bonus;
use App\Models\User;
use App\Models\UserBonus;
use App\Models\UserBonusUsedBet;
use Carbon\Carbon;
use DB;
use DragonStudio\BonusProgram\BonusProgram;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class UserBonuses extends BaseSection
{
    protected static $originalStatus;
    public $canDelete = false;
    protected $previousStatus;

    public function initialize()
    {
        $defaultAmountSetter = function ($config, UserBonus $model) {
            $userId = request()->post('user_id') ?? $model->user_id;

            $user   = User::find($userId);
            $amount = request()->post('amount');

            $model->default_amount = BaseModel::convertToDefaultCurrency($user->currency_id, $amount);

            self::$originalStatus = $model->getOriginal('status');
        };

        $bonusActivator = function ($model) {
            switch ($model->status) {
                case 'applied':
                    if (self::$originalStatus != 'active') {
                        BonusProgram::activateBonus($model, false);
                    }

                    BonusProgram::applyBonus($model);

                    break;

                case 'canceled':
                    BonusProgram::cancelBonus($model, null, self::$originalStatus);

                    break;

                case 'active':
                    BonusProgram::activateBonus($model);
            }
        };

        $this->creating($defaultAmountSetter);
        $this->updating($defaultAmountSetter);

        $this->created(function ($config, UserBonus $model) use ($bonusActivator) {
            DB::beginTransaction();
            try {
                $bonusActivator($model);
            } catch (\Exception $e) {
                DB::rollback();
                $model->delete();
                throw $e;
            }
            DB::commit();
        });

        $this->updated(function ($config, UserBonus $model) use ($bonusActivator) {
            if (self::$originalStatus == $model->status) {
                return;
            }

            DB::beginTransaction();
            try {
                $bonusActivator($model);
            } catch (\Exception $e) {
                DB::rollback();
                //$model->delete();
                throw $e;
            }
            DB::commit();
        });
    }

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
        $display->with(['user', 'bonus']);

        $display->setFilters(
            AdminDisplayFilter::custom('user_id')->setCallback(function ($query, $value) {
                $query->where('user_id', $value);
            })->setTitle('User: [:value]'),
            AdminDisplayFilter::custom('bonus_id')->setCallback(function ($query, $value) {
                $query->where('bonus_id', $value);
            })->setTitle('Bonus: [:value]'),
            AdminDisplayFilter::custom('date_from')->setCallback(function ($query, $value) {
                $query->where('created_at', '>=', Carbon::parse($value));
            })->setTitle('Date From: [:value]'),
            AdminDisplayFilter::custom('date_to')->setCallback(function ($query, $value) {
                $query->where('created_at', '<=', Carbon::parse($value)->addDay()->subSecond());
            })->setTitle('Date To: [:value]')
        );

        $display->setColumnFilters([
            null,
            AdminColumnFilter::text('number')->setOperator('contains'),
            AdminColumnFilter::sSelect(Bonus::class, 'translations.title')->setColumnName('bonus_id')->multiple(),
            AdminColumnFilterComponent::rangeInput(),
            null,
            null,
            AdminColumnFilter::sSelect()->setEnum(config('selectOptions.user_bonuses.status'))->multiple(),
            AdminColumnFilterComponent::rangeDate()
        ])->setPlacement('table.header');

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true),
            AdminColumn::sRelatedLink('bonus.title', $model)->setOrderable(true)->setMetaData(RelationsWithTranslationMetaData::class),
            //AdminColumn::sText('amount', $model),
            AdminColumn::sText('AmountFormatted', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sCustom('Wager', $model, function (UserBonus $model) {
                if ($model->wager) {
                    $data = $model->wager_data;

                    return sprintf('<strong>x%d</strong><br>(%d from %d - %d%%)',
                        $model->wager,
                        $data->current,
                        $data->total,
                        $data->total ? ceil($data->current / $data->total * 100) : 0
                    );
                }

                return '';
            })->setShowTags(true),
            AdminColumn::sCustom('Ready To Apply', $model, function (UserBonus $model) {
                return $model->is_ready_to_be_applied
                    ? '<i class="fa fa-check text-success"></i>'
                    : '';
            })->setShowTags(true)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.user_bonuses.status')),
            AdminColumn::sDateTime('expire_at', $model)->setFormat(config('selectOptions.common.dateTime'))->setWidth('150px')
                       ->setMetaData(DateTimeMetaData::class),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model       = $this->getModel();
        $table       = $model->getTable();
        $parentModel = $model->whereId($id)->first();

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sSelect('user_id', $model, User::class)->setDisplay('name')->setValidationRules(['required']),
                    AdminFormElement::sText('amount', $model)->setValidationRules(['required']),
                    AdminFormElement::sDateTime('expire_at', $model)->setDefaultValue(Carbon::now()->addDays(1))
                                    ->setPickerFormat(config('selectOptions.common.dateTime'))->setFormat(config('selectOptions.common.dateTimeDB')),
                ],
                [
                    AdminFormElement::sSelect('bonus_id', $model, Bonus::class)->setDisplay('translations.title')->setValidationRules(['required']),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.user_bonuses.status'))->setDefaultValue('active')
                                    ->setValidationRules(['required']),
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel()
        ]);

        if ($parentModel && ! in_array($parentModel->bonus_id, [2, 3])) {
            $tabs         = AdminDisplay::tabbed();
            $usedBetsList = AdminSection::getModel(UserBonusUsedBet::class)->fireDisplay(['scopes' => ['user_bonus_id' => $id, 'noFilters' => true]]);

            $tabs->appendTab($form, trans("admin/{$table}.tabs.Form"))->setIcon('<i class="fa fa-info"></i>');
            $tabs->appendTab($usedBetsList, trans("admin/{$table}.tabs.UsedBets"))->setIcon('<i class="fa fa-list"></i>');

            return $tabs;
        }

        return $form;
    }
}
