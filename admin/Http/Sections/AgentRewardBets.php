<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\AdminGetterInputMetaData;
use Admin\ColumnMetas\MorphMetaData;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;

class AgentRewardBets extends BaseSection
{
    public $canCreate = false;
    public $canDelete = false;
    public $canEdit = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'agent_reward_id')) {
                $query->where("{$model->getTable()}.agent_reward_id", array_get($scopes, 'agent_reward_id'));
            }
        });
        $display->setParameters(['agent_reward_id' => array_get($scopes, 'agent_reward_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[1, 'desc']]);
        $display->with(['player', 'bet']);

        $display->setColumnFilters([
            null,
            AdminColumnFilter::date()->setOperator('contains')->setColumnName('bet.created_at'),
            AdminColumnFilter::date()->setOperator('contains')->setColumnName('bet.created_at'),
            AdminColumnFilter::text()->setOperator('equal')->setColumnName('bet_id'),
            AdminColumnFilter::sSelect(['roulette_bet' => 'Roulette', 'baccarat_bet' => 'Baccarat'])->setColumnName('bet_type')->multiple(),
            AdminColumnFilter::text()->setOperator('equal')->setColumnName('player.name'),
            AdminColumnFilter::sSelect(['won' => 'Won', 'lost' => 'Lost', 'stay' => 'Stay'])->setColumnName('bet_result')->multiple(),
            AdminColumnFilter::text()->setOperator('equal')->setColumnName('bet.AdminAmount'),
            AdminColumnFilter::text()->setOperator('equal')->setColumnName('default_bet_bank_amount'),
            AdminColumnFilter::text()->setOperator('equal')->setColumnName('subagent_level_distance'),
            AdminColumnFilter::text()->setOperator('equal')->setColumnName('level_percent'),
            AdminColumnFilter::text()->setOperator('equal')->setColumnName('AdminRewardAmount'),
        ])->setPlacement('table.header');

        $display->setColumns([
            AdminColumn::sLink('id', '#'),
            AdminColumn::sText('bet.created_at', $model)->setMetaData(MorphMetaData::class),
            AdminColumn::sText('used_at', $model),
            AdminColumn::sRelatedLink('bet.id', $model)->setMetaData(MorphMetaData::class),
            AdminColumn::sText('bet_type', $model),
            AdminColumn::sRelatedLink('player.name', $model),
            AdminColumn::sText('bet_result', $model),
            AdminColumn::sText('bet.AdminAmount', $model)->setMetaData(AdminGetterInputMetaData::class)->setMetaData(MorphMetaData::class),
            AdminColumn::sText('default_bet_bank_amount', $model),
            AdminColumn::sText('subagent_level_distance', $model),
            AdminColumn::sText('level_percent', $model),
            AdminColumn::sText('AdminRewardAmount', $model)->setMetaData(AdminGetterInputMetaData::class)->setMetaData(MorphMetaData::class),
        ]);

        return $display;
    }
}