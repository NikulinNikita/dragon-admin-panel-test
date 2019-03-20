<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\Custom\PartnerProgram\PartnerProgram;
use Admin\Http\Sections\PageModels\AgentBet;
use AdminColumn;
use AdminDisplay;
use AdminDisplayFilter;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Models\Agent;
use App\Models\AgentLink;
use App\Models\BaseModel;
use App\Models\User;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\FormElements;

class Agents extends BaseSection
{
    public $canCreate = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'type') === 'agents') {
                $query->where("{$model->getTable()}.parent_id", array_get($scopes, 'agent_id') ?? 0);
            }
            if (array_get($scopes, 'type') === 'subAgents') {
                $query->whereIn("{$model->getTable()}.id", array_get($scopes, 'subAgentsIds') ?? [0]);
            }
        });
        $display->setParameters([
            'agents'       => array_get($scopes, 'agents'),
            'subAgents'    => array_get($scopes, 'subAgents'),
            'agent_id'     => array_get($scopes, 'agent_id'),
            'subAgentsIds' => array_get($scopes, 'subAgentsIds'),
        ]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['user.userTills', 'children', 'descendants', 'agentRewardBets']);

        $display->setFilters(
            AdminDisplayFilter::custom('agentId')->setCallback(function ($query, $value) {
                return $query->where('id', $value);
            })->setTitle('Agent ID: [:value]'),
            AdminDisplayFilter::custom('date_from')->setCallback(function ($query, $value) {
                $query;
            })->setTitle('Date From: [:value]'),
            AdminDisplayFilter::custom('date_to')->setCallback(function ($query, $value) {
                $query;
            })->setTitle('Date To: [:value]')
        );

        $columns = [
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(BaseMetaData::class),
            AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true),
        ];
        if ( ! array_get($scopes, 'type')) {
            $columns = array_merge($columns, [
                AdminColumn::sText('AdminAgents', $model)->setMetaData(BaseMetaData::class)->setOrderable(false),
                AdminColumn::sCustom('AdminAgentBetsBank', $model, function (Agent $model) {
                    return sprintf('<a href="%s">%s</a>', $this->getEditUrl($model->id, ['tab' => 'agent']), $model->AdminAgentBetsBank);
                })->setShowTags(true),
                AdminColumn::sText('AdminAgentRewardsAmount', $model)->setMetaData(BaseMetaData::class)->setOrderable(false),
                AdminColumn::sText('AdminSubAgents', $model)->setMetaData(BaseMetaData::class)->setOrderable(false),
                AdminColumn::sCustom('AdminSubAgentBetsBank', $model, function (Agent $model) {
                    return sprintf('<a href="%s">%s</a>', $this->getEditUrl($model->id, ['tab' => 'subagent']), $model->AdminSubAgentBetsBank);
                })->setShowTags(true),
                AdminColumn::sText('AdminSubAgentRewardsAmount', $model)->setMetaData(BaseMetaData::class)->setOrderable(false),
                AdminColumn::sText('AdminPartnerTillBalance', $model)->setMetaData(BaseMetaData::class)->setOrderable(false),
                AdminColumn::sText('AdminUsedPartnerPoints', $model)->setMetaData(BaseMetaData::class)->setOrderable(false),
                AdminColumn::sText('AdminMoneyTillBalance', $model)->setMetaData(BaseMetaData::class)->setOrderable(false),
            ]);
        } else {
            $agent = Agent::find(array_get($scopes, 'agent_id') ?? array_get($scopes, 'root_id'));

            $columns = array_merge($columns, [
                AdminColumn::sCustom('AdminParticularBets', $model, function (Agent $model) use ($scopes, $agent) {
                    return sprintf('<a href="%s">%s</a>',
                        route('admin.agents.betsbank.rounds', ['parent_id' => $agent->user_id, 'player_id' => $model->user_id]),
                        /*BaseModel::generateUrl(AgentBet::class, [
                            'parentAgentId' => array_get($scopes, 'agent_id') ?? array_get($scopes, 'root_id'),
                            'playerId'      => $agent->user_id,
                            'type'          => array_get($scopes, 'agent_id') ? 'agent' : 'subagent',
                        ]),*/
                        $model->AdminParticularBets
                    );
                })->setShowTags(true),
                AdminColumn::sCustom('AdminParticularAgentBetsBank', $model, function (Agent $model) use ($scopes, $agent) {
                    return sprintf('<a href="%s">%s</a>',
                        route('admin.agents.betsbank.rounds', ['parent_id' => $agent->user_id, 'player_id' => $model->user_id]),
                        /*BaseModel::generateUrl(AgentBet::class, [
                            'parentAgentId' => array_get($scopes, 'agent_id') ?? array_get($scopes, 'root_id'),
                            'playerId'      => $agent->user_id,
                            'type'          => array_get($scopes, 'agent_id') ? 'agent' : 'subagent',
                        ]),*/
                        $model->AdminParticularAgentBetsBank
                    );
                })->setShowTags(true),
                AdminColumn::sText('AdminParticularRewardPercent', $model)->setMetaData(BaseMetaData::class)->setOrderable(false),
                AdminColumn::sText('AdminParticularAgentRewards', $model)->setMetaData(BaseMetaData::class)->setOrderable(false),
            ]);

            // Messy code
            if ($data = $this->getTotalColumnData($scopes)) {
                $display->setColumnsTotal([
                    null,
                    '<b>' . trans("admin/common.Total") .':</b>',
                    null,
                    '<b>' . BaseModel::formatCurrency(1, $data['totals'][0]) . '</b>',
                    '<b>' . BaseModel::formatCurrency(1, $data['totals'][1]) . '</b>',
                    null,
                    '<b>' . BaseModel::formatCurrency(1, $data['totals'][2])
                        . (1 != $data['agent']->user->currency_id ? ' / ' . BaseModel::convertDefaultCurrencyAndFormat($data['agent']->user->currency_id, $data['totals'][2]) : '')
                        .  '</b>',
                    null
                ],
                    $display->getColumns()->all()->count()
                );
                $display->getColumnsTotal()->setPlacement('table.footer');
            }
        }
        $display->setColumns($columns);

        if ( ! array_get($scopes, 'type')) {
            $tabs   = AdminDisplay::tabbed();
            $search = AdminForm::panel()->setView(view('admin::search.agents'));

            $tabs->appendTab($display, trans("admin/{$table}.tabs.List"))->setIcon('<i class="fa fa-info"></i>');
            $tabs->appendTab($search, trans("admin/{$table}.tabs.Search"))->setIcon('<i class="fa fa-info"></i>');

            return $tabs;
        }

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sSelect('user_id', $model, User::class)->setDisplay('name')->required()->setReadonly(true),
                ],
                [
                    //
                ]
            ]),
            new  FormElements(['<hr>']),
            ! is_null($id) ? AdminSection::getModel(AgentLink::class)->fireDisplay(['scopes' => ['user_id' => $id]]) : ''
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        $tabs = AdminDisplay::tabbed();
        $tabs->appendTab($form, trans("admin/{$table}.tabs.Agent"))->setIcon('<i class="fa fa-info"></i>');
        if ( ! is_null($id)) {
            $subAgentsIds = PartnerProgram::getSubAgentsIds($id, null, false, false, true);

            $agents       = AdminSection::getModel(Agent::class)->fireDisplay([
                'scopes' => [
                    'type' => 'agents',
                    'agent_id' => $id
                ]
            ]);
            $subAgents    = AdminSection::getModel(Agent::class)->fireDisplay([
                'scopes' => [
                    'type'         => 'subAgents',
                    'subAgentsIds' => $subAgentsIds,
                    'root_id'      => $id
                ]
            ]);

            $activeTab = request('tab');

            $tabs->appendTab($agents, trans("admin/{$table}.tabs.Agents"), 'agent' == $activeTab)->setIcon('<i class="fa fa-credit-card"></i>');
            $tabs->appendTab($subAgents, trans("admin/{$table}.tabs.SubAgents"), 'subagent' == $activeTab)->setIcon('<i class="fa fa-credit-card"></i>');
        }

        return $tabs;
    }

    protected function getTotalColumnData(array $scope)
    {
        if (!isset($scope['agent_id']) && !isset($scope['root_id'])) {
            return false;
        }

        $parentAgent = Agent::with('user')->find($scope['agent_id'] ?? $scope['root_id']);

        if (isset($scope['agent_id'])) {
            $subagentIds = $this->getModel()->query()->select('user_id')
                ->where('parent_id', $parentAgent->id)
                ->get()
                ->pluck('user_id')
                ->toArray();
        } else {
            if (!isset($scope['subAgentsIds'])) {
                return false;
            }

            $subagentIds = Agent::select('user_id')
                ->whereIn('id', $scope['subAgentsIds'])
                ->get()
                ->pluck('user_id')
                ->toArray();
        }

        return [
            'agent' => $parentAgent,
            'totals' => self::countTotals($parentAgent->user_id, $subagentIds)
        ];
    }

    protected static function countTotals(int $topLevelUserId, array $subagentUserIds): array
    {
        $data = PartnerProgram::getData($topLevelUserId);

        $totalBets = $totalBetsBank = $totalRewards = 0;

        if (isset($data[$topLevelUserId])) {
            foreach ($data[$topLevelUserId]['rounds'] as $round) {
                if ($round['agent_id'] != $topLevelUserId
                    || !in_array($round['player_id'], $subagentUserIds)) {
                    continue;
                }

                $totalBets+= $round['statistics']['betsAmount'];
                $totalBetsBank+= $round['statistics']['betsBankAmount'];
                $totalRewards+= $round['statistics']['rewardAmount'];
            }
        }

        return [$totalBets, $totalBetsBank, $totalRewards];
    }
}
