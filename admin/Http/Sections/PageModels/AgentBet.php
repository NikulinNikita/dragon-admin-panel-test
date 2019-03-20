<?php

namespace Admin\Http\Sections\PageModels;

use Admin\Custom\PartnerProgram\PartnerProgram;
use App\Models\Agent;
use App\Models\BaseModel;

class AgentBet extends BaseModel
{
    public static function getAdminBetsArray($parentAgentId, $playerId, $type = 'agent')
    {
        $baccaratBets = $rouletteBets = [];
        $parentId     = Agent::whereId($parentAgentId)->first()->user_id;
        $playerId     = (int)$playerId;
        $levels       = $type === 'agent' ? [1] : range(2, PartnerProgram::getNetworkDepth());

        $data = PartnerProgram::getData($parentId, $levels);

        if ( ! isset($data[$parentId])) {
            return 0;
        }

        foreach ($data[$parentId]['rounds'] as $round) {
            if ($round['player_id'] != $playerId
                || $round['agent_id'] != $parentId) {
                continue;
            }

            if (isset($round['round']['baccarat_shoe_id'])) {
                $baccaratBets = array_merge($baccaratBets, array_column($round['bets'], 'id'));
            } elseif (isset($round['round']['roulette_cell_id'])) {
                $rouletteBets = array_merge($rouletteBets, array_column($round['bets'], 'id'));
            }
        }

        return [
            'baccaratBetIds' => $baccaratBets,
            'rouletteBetIds' => $rouletteBets
        ];
    }
}
