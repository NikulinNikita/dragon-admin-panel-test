<?php

namespace Admin\Http\Sections;

use AdminDisplay;

use AdminColumn;

use AdminSection;

use AdminColumnFilter;

use AdminForm;
use AdminFormElement;

use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

use App\Models\BonusReward;
use App\Models\BonusRewardWagerBet;

class BonusRewardBet extends BaseSection
{
    public $canCreate = false;
    public $canDelete = false;
    public $canEdit   = false;

    public function onDisplay($scopes = [])
    {
        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        
        $display
            ->paginate(config('selectOptions.common.adminPagination'))
            ->setOrder([[0, 'desc']]);
        
        //$display->with('bet');

        $display->setColumns([
            AdminColumn::relatedLink('id', '#'),
            AdminColumn::text('bet_type', 'Bet type'),
        ]);
        
        return $display;
    }
}