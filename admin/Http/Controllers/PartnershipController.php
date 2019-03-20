<?php

namespace Admin\Http\Controllers;

use Illuminate\Http\Request;

use AdminSection;

use App\Models\Setting;
use App\Models\AgentRewardLimit;
use App\Rules\NumericArray;

class PartnershipController extends Controller
{
    const DEFAULT_LEVEL_VALUE = 0.01;

    private $partnershipSettingsColumns = [
        'partnership_network_depth',
        'partnership_level_percent',
        'partnership_reward_run_days',
        'partnership_reward_run_hour',
    ];

    public function index()
    {
        $partnershipSettings         = $this->getPartnershipSettings();
        $agentRewardLimits           = $this->getAgentRewardLimits();
        $maxAutomaticProcessingLimit = $this->getMaxAutomaticProcessingLimit();

        $content = view(
            'admin.partnership.index',
            [
                'partnership_settings'           => $partnershipSettings,
                'agent_reward_limits'            => $agentRewardLimits,
                'max_automatic_processing_limit' => $maxAutomaticProcessingLimit
            ]
        );

        return AdminSection::view($content, trans("admin/partnership.PartnershipSettings"));
    }

    public function update(Request $request)
    {
        $validateData = $request->validate($this->rules());

        $partnershipSettings         = $request->all();
        $agentRewardLimits           = $request->get('agent_reward_limits');
        $maxAutomaticProcessingLimit = $request->get('max_automatic_processing_limit');

        $this->savePartnershipSettings($partnershipSettings);
        $this->saveAgentRewardLimits($agentRewardLimits);
        $this->saveMaxAutomaticProcessingLimit($maxAutomaticProcessingLimit);

        return redirect()->back()->send();
    }

    private function getPartnershipSettings()
    {
        $settings = Setting::whereIn('key', $this->partnershipSettingsColumns)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
        
        $settings['partnership_level_percent'] = json_decode(
            $settings['partnership_level_percent']
        );

        return $settings;
    }

    private function savePartnershipSettings(array $settings = [])
    {
        if (!$settings) return;

        try {
            $countLevel = count($settings['partnership_level_percent']);

            // Set default value for new levels
            if ($settings['partnership_network_depth'] > $countLevel) {
                $countNewLevels = $settings['partnership_network_depth'] - $countLevel;
                
                $newLevels = array_fill(0, $countNewLevels, self::DEFAULT_LEVEL_VALUE);

                $settings['partnership_level_percent'] = array_merge(
                    $settings['partnership_level_percent'],
                    $newLevels
                );
            }

            $settings['partnership_level_percent'] = json_encode(
                $settings['partnership_level_percent']
            );

            foreach ($settings as $key => $value) {
                if (in_array($key, $this->partnershipSettingsColumns)) {
                    \DB::table('settings')
                        ->where('key', $key)
                        ->update(['value' => $value]);
                }
            }

            return redirect()->back()->with('success', 'Records has been updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Records were not updated due to an error');
        }
    }

    private function getAgentRewardLimits()
    {
        return AgentRewardLimit::where('type', 'min')->get();
    }

    private function saveAgentRewardLimits(array $settings = [])
    {
        if (!$settings) return false;

        foreach ($settings as $currency_id => $value) {
            \DB::table('agent_reward_limits')
                ->where(['currency_id' => $currency_id, 'type' => 'min'])
                ->update(['value' => $value]);
        }
    }

    private function getMaxAutomaticProcessingLimit()
    {
        return AgentRewardLimit::where('type', 'max_automatic_processing_limit')->get();
    }

    private function saveMaxAutomaticProcessingLimit(array $settings = [])
    {
        if (!$settings) return false;

        foreach ($settings as $currency_id => $value) {
            \DB::table('agent_reward_limits')
                ->where([
                    'currency_id' => $currency_id,
                    'type'        => 'max_automatic_processing_limit'
                ])
                ->update(['value' => $value]);
        }
    }

    private function rules()
    {
        return [
            'partnership_network_depth' => 'required|integer',
            'partnership_reward_run_days' => 'regex:/\d+,?\.?/',
            'partnership_reward_run_hour' => 'date_format:H:i',
            'partnership_level_percent' => new NumericArray,
            'agent_reward_limits' => new NumericArray,
            'max_automatic_processing_limit' => new NumericArray,
        ];
    }
}