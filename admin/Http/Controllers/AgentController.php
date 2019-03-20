<?php

namespace Admin\Http\Controllers;

use App\Models\Agent;
use App\Models\BaseModel;

use Admin\Custom\PartnerProgram\PartnerProgram;
use App\Models\User;
use Carbon\Carbon;

class AgentController extends Controller
{
    public function reportsByRoundPage($agentUserId, $playerUserId)
    {
        $agentUser = User::find($agentUserId);
        $playerUser = User::find($playerUserId);

        if (!$agentUser || !$playerUser) {
            abort(404);
        }

        /*
        $data = PartnerProgram::getData($agentUserId);

        $rounds = [];
        foreach ($data as $roundData) {
            foreach ($roundData['rounds'] as $round) {
                if ($round['playerAgent']['user_id'] != $playerUserId
                    || $round['agent']['user_id'] != $agentUserId) {
                    continue;
                }

                $rounds[] = $round;
            }
        }
        */

        // better memory utilization
        $rounds = PartnerProgram::getRounds($agentUser->agent, $playerUser->agent);

        $view = view('admin::agent.rounds-report', [
            'rounds' => $rounds,
            'agentUser' => $agentUser,
            'playerUser' => $playerUser
        ]);

        return \AdminSection::view($view, trans('admin/agents.page_title'));
    }

    public function getSubAgents($parent_id)
    {
        $subAgents = Agent::with('children', 'user')
            ->where('parent_id', $parent_id)
            ->get();

        $period = request()->get('date_from') && request()->get('date_to')
            ? [Carbon::parse(request()->get('date_from'))->setTime(0, 0), Carbon::parse(request()->get('date_to'))->setTime(23, 59, 59)]
            : null;

        $firstLevelPercent = PartnerProgram::getLevelPercentage(1);
        $secondLevelPercent = PartnerProgram::getLevelPercentage(2);

        $statistics = [];
        foreach ($subAgents as $subAgent) {
            $ownBetsBank = PartnerProgram::calculateOwnBetsBank($subAgent->user_id, $period);

            $betsBank     = PartnerProgram::calculateBetsBank($subAgent->user_id, $period, [1]);
            $rewardAmount = PartnerProgram::calculateRewardAmount($subAgent->user_id, $period, [1]);

            $subAgentsBetsBank     = PartnerProgram::calculateBetsBank($subAgent->user_id, $period, range(2, PartnerProgram::getNetworkDepth()));
            $subAgentsRewardAmount = PartnerProgram::calculateRewardAmount($subAgent->user_id, $period, range(2, PartnerProgram::getNetworkDepth()));

            $agentsCount    = count($subAgent->children);
            $subAgentsCount = count($subAgent->descendants) - count($subAgent->children);

            $statistics[$subAgent->id] = [
                'ownBetsBank'             => BaseModel::formatCurrency(1, $ownBetsBank),

                'agentsCount'             => $agentsCount,
                'betsBank'                => BaseModel::formatCurrency(1, $betsBank),
                'firstLevelRewardPercent' => $firstLevelPercent,
                'rewardAmount'            => BaseModel::formatCurrency(1, $rewardAmount),

                'subAgentsCount'          => $subAgentsCount,
                'subAgentsBetsBank'       => BaseModel::formatCurrency(1, $subAgentsBetsBank),
                'subAgentRewardPercent'   => $secondLevelPercent,
                'subAgentsRewardAmount'   => BaseModel::formatCurrency(1, $subAgentsRewardAmount)
            ];
        }

        $html = view('admin::agent.subagents', ['subAgents' => $subAgents, 'statistics' => $statistics])
            ->render();

        return response()->json(['html' => $html]);
    }
}