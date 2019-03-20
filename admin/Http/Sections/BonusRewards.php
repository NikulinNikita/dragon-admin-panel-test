<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\DateTimeMetaData;
use App\Models\BaccaratBet;
use App\Models\RouletteBet;
use DB;
use Admin\ColumnMetas\AdminGetterInputMetaData;
use Admin\ColumnMetas\RelationsWithTranslationMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Models\BaseModel;
use App\Models\BetsBankAccrual;
use App\Models\Bonus;
use App\Models\BonusReward;
use App\Models\User;
use App\Models\UserBonus;
use App\Traits\ExchangeCurrencyFunctionsTrait;
use DragonStudio\BonusProgram\BonusHelper;
use DragonStudio\BonusProgram\BonusProgram;
use Illuminate\Validation\ValidationException;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class BonusRewards extends BaseSection
{
    use ExchangeCurrencyFunctionsTrait;

    public $canCreate = false;
    public $canDelete = false;

    protected static $originalStatus;
    protected static $newStatus;

    public function initialize()
    {
        $this->updating(function ($config, $model) {
            self::$originalStatus = $model->status;

            if ($model->status != request()->post('status')) {
                // we save the statuses to variable
                // and will actually change the status if everything went smoothly
                self::$newStatus = request()->post('status');
            }
        });

        $this->updated(function($config, $model) {
            if (!self::$newStatus || !in_array(self::$newStatus, ['active', 'paid'])) {
                return;
            }

            // hacky
            $model->amount = request()->post('amount') ?? $model->amount;
            $model->status = self::$originalStatus;
            $model->save();

            if ('paid' == self::$newStatus) {
                $maxPossibleRewardAmount = $this->getMaxRewardAmount($model);

                if (false !== $maxPossibleRewardAmount && $model->amount > $maxPossibleRewardAmount) {
                    \MessagesStack::addError(trans('admin/bonus_rewards.insufficient_bets_error'));
                    throw new ValidationException(1);
                }
            }

            try {
                DB::transaction(function() use ($model) {
                    $rewardStatusesToBonusStatusesMap = [
                        'active' => 'active',
                        'paid' => 'applied'
                    ];

                    $attributes = [];

                    if ('bets_amount_bonus' == $model->bonus->name) {
                        $attributes['betting_period_start'] = BonusProgram::getBonusTypeClass($model->bonus->name)
                            ->getBettingPeriodEnd($model->user_id);
                        $attributes['betting_period_end'] = now()->toDateTimeString();
                    }

                    BonusHelper::convertBonusRewardToUserBonus($model, $rewardStatusesToBonusStatusesMap[self::$newStatus], $attributes);
                });
            } catch (\Exception $e) {
                \MessagesStack::addError($e->getMessage());
                throw new ValidationException(1);
            }
        });
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['user', 'bonus']);

        $display->setColumnFilters([
            AdminColumnFilter::sSelect(BonusReward::class, 'id')->multiple(),
            AdminColumnFilter::sSelect(User::class, 'name')->setColumnName('user_id')->multiple(),
            AdminColumnFilter::sSelect(Bonus::class, 'translations.title')->setColumnName('bonus_id')->multiple(),
            AdminColumnFilterComponent::rangeInput(),
            AdminColumnFilter::sSelect()->setEnum(config('selectOptions.bonus_rewards.status'))->multiple(),
            AdminColumnFilterComponent::rangeDate(),
        ])->setPlacement('table.header');

        $display->setColumns([
            AdminColumn::sLink('id', $model),
            AdminColumn::sText('user.name', $model),
            AdminColumn::sText('bonus.title', $model)->setMetaData(RelationsWithTranslationMetaData::class),
            AdminColumn::sText('AdminAmount', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('status', $model),
            AdminColumn::sDateTime('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model       = $this->getModel();
        $table       = $model->getTable();
        $parentModel = $model->whereId($id)->first();

        $statuses = config('selectOptions.bonus_rewards.status');

        if ($parentModel->status != 'paid') {
            $maxPossibleRewardAmount = $this->getMaxRewardAmount($parentModel);
            if (false !== $maxPossibleRewardAmount) {
                if ($maxPossibleRewardAmount < $parentModel->amount) {
                    unset($statuses['paid']);
                    \MessagesStack::addWarning(trans('admin/bonus_rewards.insufficient_bets_notif',
                        ['amount' => BonusReward::formatWithDecimals($parentModel->user->currency_id, $maxPossibleRewardAmount)]));
                } else {
                    \MessagesStack::addInfo(trans('admin/bonus_rewards.max_reward_amount_notif',
                        ['amount' => BonusReward::formatWithDecimals($parentModel->user->currency_id, $maxPossibleRewardAmount)]));
                }
            }
        }

        $amountField = AdminFormElement::sText('amount', $model);

        switch ($parentModel->status) {
            case 'paid':
                unset($statuses['pending'], $statuses['canceled'], $statuses['active']);
                $amountField->setReadonly(true);
                break;
            case 'active':
                unset($statuses['pending'], $statuses['canceled']);
                $amountField->setReadonly(true);
        }

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sSelect('status', $model)->required()->setEnum($statuses),
                    AdminFormElement::sSelect('user_id', $model, User::class)->setDisplay('name')->setReadOnly(true),
                    AdminFormElement::sSelect('bonus_id', $model, Bonus::class)->setDisplay('title')->setReadOnly(true),
                ],
                [
                    AdminFormElement::sText('created_at', $model)->setReadOnly(true),
                    $amountField
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'cancel' => new Cancel(),
        ]);

        if ( ! $parentModel || ($parentModel && !in_array($parentModel->bonus_id, [2, 3]))) {
            $tabs            = AdminDisplay::tabbed();
            $betsBankAccrual = AdminSection::getModel(BetsBankAccrual::class)
                                           ->fireDisplay(['scopes' => ['user_id' => $parentModel->user_id, 'nonUsed' => true]]);

            $tabs->appendTab($form, trans("admin/{$table}.tabs.info"))->setIcon('<i class="fa fa-info"></i>');
            $tabs->appendTab($betsBankAccrual, trans("admin/{$table}.tabs.bets"))->setIcon('<i class="fa fa-credit-card"></i>');

            return $tabs;
        }

        return $form;
    }

    protected function getMaxRewardAmount(BonusReward $bonusReward): float
    {
        $bonusReward->load('bonus', 'user');

        $bonusInstance = BonusProgram::getBonusTypeClass($bonusReward->bonus->name);

        switch ($bonusReward->bonus->name) {
            case 'bets_amount_bonus':
                $period = [$bonusInstance->getBettingPeriodEnd($bonusReward->user_id), now()];

                $betsBankAmount = $bonusInstance->getBetsBankAmount($bonusReward->user_id, $period);

                return $bonusInstance->processBonusAmount($bonusReward->user, $betsBankAmount);

            case 'discount':
                $options = json_decode($bonusReward->options);

                $period = [$options->period[0], $options->period[1]];

                $netLoss = [
                    'baccarat' => $bonusInstance->calculateNetLoss(BaccaratBet::class, $bonusReward->user, $period),
                    'roulette' => $bonusInstance->calculateNetLoss(RouletteBet::class, $bonusReward->user, $period),
                ];

                $netLossAmount = max($netLoss['baccarat'], $netLoss['roulette']);

                return $bonusInstance->processBonusAmount($bonusReward->user, $netLossAmount);
        }

        return false;
    }
}
