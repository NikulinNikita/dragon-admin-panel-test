<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\DateTimeMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Events\User\UserTillBalanceChange;
use App\Models\AgentReward;
use App\Models\AgentRewardBet;
use App\Models\BaseModel;
use App\Models\Operation;
use App\Models\User;
use App\Models\UserTill;
use Carbon\Carbon;
use DB;
use Illuminate\Validation\ValidationException;
use MessagesStack;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\FormElements;
use SleepingOwl\Admin\Section;

class AgentRewards extends BaseSection
{
    public $canCreate = false;
    public $canDelete = false;

    public function initialize()
    {
        $this->updating(function (Section $config, AgentReward $model) {
            if (in_array($model->status, ['cancelled', 'paid'])) {
                MessagesStack::addError("Item already has status {$model->status}!");
                $hasValidationError = true;
            } else {
                $attributes = request()->except('sleeping_owl_tab_id', '_method', '_redirectBack', '_token', 'next_action', 'user');
                $model->setRawAttributes($attributes + $model->getAttributes());
            }
            if ($hasValidationError ?? false) {
                throw new ValidationException(1);
            }

            if ($model->getOriginal('status') !== $model->status) {
                if (in_array($model->status, ['paid'])) {
                    DB::transaction(function () use ($config, $model) {
                        $model->agentRewardBets()->where('used_at', null)->update(['used_at' => Carbon::now()]);

                        $userTills = UserTill::whereIn('id', [$model->user->MoneyTill->id, $model->user->PartnerTill->id])->get();

                        $partnerTill = $userTills->where('till_id', 3)->first();
                        $amount      = -$model->amount;
                        $balance     = $partnerTill->balance + $amount;
                        $params      = [
                            'staff_id'        => auth()->id(),
                            'created_at'      => $model->created_at,
                            'operatable_type' => $model->type === 'agent' ? 'agents_reward' : 'subagents_reward',
                            'operatable_id'   => $model->id,
                        ];
                        Operation::saveNewOperationAndUserTillBalance($model, $partnerTill, $amount, $balance, $params);

                        $moneyTill = $userTills->where('till_id', 1)->first();
                        $amount    = $model->amount;
                        $balance   = $moneyTill->balance + $amount;
                        $params    = [
                            'staff_id'        => auth()->id(),
                            'created_at'      => $model->created_at,
                            'operatable_type' => $model->type === 'agent' ? 'agents_reward' : 'subagents_reward',
                            'operatable_id'   => $model->id,
                        ];
                        Operation::saveNewOperationAndUserTillBalance($model, $moneyTill, $amount, $balance, $params);

                        $model->balance         = BaseModel::convertToDefaultCurrency($moneyTill->currency_id, $moneyTill->balance);
                        $model->default_balance = BaseModel::convertToDefaultCurrency($moneyTill->currency_id, $moneyTill->default_amount);
                        $model->save();

                        $userTillBalanceChangeEvent = new UserTillBalanceChange($moneyTill);
                        broadcast($userTillBalanceChangeEvent);
                    });
                }
            }

            return true;
        });
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[1, 'desc'], [0, 'desc']]);
        $display->with(['user.currency']);

        $display->setColumnFilters([
            null,
            AdminColumnFilterComponent::rangeDate(),
            AdminColumnFilter::sSelect(User::class, 'name')->setColumnName('user_id')->multiple(),
            AdminColumnFilterComponent::rangeInput(),
            AdminColumnFilterComponent::rangeInput(),
            AdminColumnFilterComponent::rangeInput(),
            AdminColumnFilterComponent::rangeInput(),
            AdminColumnFilter::sSelect()->setEnum(config('selectOptions.agent_rewards.type'))->multiple(),
            AdminColumnFilter::sSelect()->setEnum(config('selectOptions.agent_rewards.status'))->multiple(),
        ])->setPlacement('table.header');

        $display->setColumns([
            AdminColumn::sLink('id', '#'),
            AdminColumn::sDateTime('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
            AdminColumn::sRelatedLink('user.name', $model),
            AdminColumn::sCustom('amount', $model, function ($model) {
                return BaseModel::formatCurrency($model->user->currency_id, $model->amount);
            })->setOrderable(true),
            AdminColumn::sCustom('balance', $model, function ($model) {
                return BaseModel::formatCurrency($model->user->currency_id, $model->balance);
            })->setOrderable(true),
            AdminColumn::sCustom('default_amount', $model, function ($model) {
                $url    = self::getUrl($this->getClass()) . '/' . $model->id . '/edit';
                $amount = BaseModel::formatCurrency(1, $model->default_amount);

                return sprintf('<a href="%s">%s</a>', $url, $amount);
            })->setOrderable(true)->setShowTags(true),
            AdminColumn::sCustom('default_balance', $model, function ($model) {
                return BaseModel::formatCurrency($model->user->currency_id, $model->default_balance);
            })->setOrderable(true),
            AdminColumn::sText('type', $model),
            AdminColumn::sText('status', $model)
        ]);

        $this->addCustomActionButton($display, 'edit', 'search');

        return $display;
    }

    public function onEdit($id)
    {
        $model    = $this->getModel();
        $instance = $model->whereId($id)->first();

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('user.name', $model)->setReadonly(true),
                    AdminFormElement::sText('created_at', $model)->setReadonly(true),
                ],
                [
                    AdminFormElement::sText('amount', $model)->setReadonly(true),
                    AdminFormElement::sText('default_amount', $model)->setReadonly(true),
//                    AdminFormElement::sText('balance', $model)->setReadonly(true),
//                    AdminFormElement::sText('default_balance', $model)->setReadonly(true),
                ],
                [
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.agent_rewards.status'))->required()
                                    ->setReadonly(! in_array($instance->status, ['pending'])),
                    AdminFormElement::sText('type', $model)->setReadonly(true),
                ],
            ]),
            new  FormElements(['<hr>']),
            ! is_null($id) ? AdminSection::getModel(AgentRewardBet::class)->fireDisplay(['scopes' => ['agent_reward_id' => $id]]) : ''
        )->getButtons()->setButtons([
            'save'   => in_array($instance->status, ['pending']) ? (new SaveAndClose()) : false,
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);


        return $form;
    }

    public function getEditTitle()
    {
        return 'Rewards on bets #' . \Request::segment(3);
    }
}