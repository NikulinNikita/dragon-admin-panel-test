<?php

namespace Admin\Http\Sections;

use AdminDisplay;

use AdminColumn;

class BonusRewardBets extends BaseSection
{
    public $canEdit = false;
    public $canDelete = false;
    public $canCreate = false;
    
    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        
        $display
            ->paginate(config('selectOptions.common.adminPagination'))
            ->setOrder([[0, 'desc']]);
        
        $display->with('bet');

        $display->setColumns([
            AdminColumn::sText('bet_type', $model),
            AdminColumn::sRelatedLink('bet.id', $model),
            AdminColumn::sText('bet.amount', $model),
            AdminColumn::sText('bet.default_amount', $model),
            AdminColumn::sText('bet.created_at', $model),
        ]);

        if (array_key_exists('bonus_reward_id', $scopes)) {
            $display->setApply(function ($query) use($scopes) {
                $query->where('bonus_reward_id', $scopes['bonus_reward_id']);
            });
        }

        return $display;
    }
}