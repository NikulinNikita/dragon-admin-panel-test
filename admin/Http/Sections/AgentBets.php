<?php

namespace Admin\Http\Sections;

use Admin\Http\Sections\PageModels\AgentBet;
use AdminDisplay;
use AdminSection;
use App\Models\BaccaratBet;
use App\Models\RouletteBet;

class AgentBets extends BaseSection
{
    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $tabs = AdminDisplay::tabbed();

        ['parentAgentId' => $parentAgentId, 'playerId' => $playerId, 'type' => $type] = request()->all();
        ['baccaratBetIds' => $baccaratBetIds, 'rouletteBetIds' => $rouletteBetIds] = AgentBet::getAdminBetsArray($parentAgentId, $playerId, $type);
        $baccaratBets = AdminSection::getModel(BaccaratBet::class)->fireDisplay(['scopes' => ['baccaratBetIds' => $baccaratBetIds !== [] ? $baccaratBetIds : [0]]]);
        $rouletteBets = AdminSection::getModel(RouletteBet::class)->fireDisplay(['scopes' => ['rouletteBetIds' => $rouletteBetIds !== [] ? $rouletteBetIds : [0]]]);

        $tabs->appendTab($baccaratBets, trans("admin/{$table}.tabs.BaccaratBets"))->setIcon('<i class="fa fa-info"></i>');
        $tabs->appendTab($rouletteBets, trans("admin/{$table}.tabs.RouletteBets"))->setIcon('<i class="fa fa-info"></i>');

        return $tabs;
    }
}
