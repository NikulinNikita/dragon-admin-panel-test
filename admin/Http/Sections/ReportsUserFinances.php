<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\DateTimeMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use App\Models\Currency;
use App\Models\User;

class ReportsUserFinances extends BaseSection
{
    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[13, 'desc']]);
        $display->with(['user', 'currency']);

        if ( ! array_get($scopes, 'noFilters')) {
            $alias      = $this->getAlias();
            $buttonType = ["export", "reGenerateReport"];
            $params     = [
                'separateToSheets' => json_encode([
                    ['Money Balance', 16],
                    ['Bonus Balance', 9],
                    ['Partner Balance', 9],
                    ['Money Balance ($)', 16],
                    ['Bonus Balance ($)', 9],
                    ['Partner Balance ($)', 9],
                ])
            ];
            $display->getActions()->setView(view('admin::datatables.toolbar', compact('alias', 'buttonType', 'params')))->setPlacement('panel.heading.actions');

            $display->setColumnFilters([
                null,
                AdminColumnFilter::sSelect(User::class, 'name')->setColumnName('user_id')->multiple(),
                AdminColumnFilter::sSelect(Currency::class, 'code')->setColumnName('currency_id')->multiple(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeDate(),
            ])->setPlacement('table.header');
        }

        $exportReport = request()->get('includeHiddenColumns') || strpos(request()->url(), 'exportReport');
        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true),
            AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
            AdminColumn::sText('balance_before', $model),
            AdminColumn::sText('deposits_amount', $model),
            AdminColumn::sText('baccarat_bets_amount', $model),
            AdminColumn::sText('roulette_bets_amount', $model),
            AdminColumn::sText('used_bonuses_amount', $model),
            AdminColumn::sText('used_partners_amount', $model),
            AdminColumn::sText('baccarat_bets_outcome', $model),
            AdminColumn::sText('roulette_bets_outcome', $model),
            AdminColumn::sText('withdrawals_amount', $model),
            AdminColumn::sText('balance_after', $model),
            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
        ]);

        if ($exportReport) {
            $display->setColumns([
                AdminColumn::sText('bets_wins_alteration', $model),
                AdminColumn::sText('balance_alteration', $model),

                AdminColumn::sLink('id', '#')->setWidth('30px'),
                AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true),
                AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
                AdminColumn::sText('bonuses_balance_before', $model),
                AdminColumn::sText('bonuses_amount', $model),
                AdminColumn::sText('used_bonuses_amount', $model),
                AdminColumn::sText('canceled_bonuses_amount', $model),
                AdminColumn::sText('bonuses_balance_after', $model),
                AdminColumn::sText('created_at', $model)->setWidth('150px'),

                AdminColumn::sLink('id', '#')->setWidth('30px'),
                AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true),
                AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
                AdminColumn::sText('partners_balance_before', $model),
                AdminColumn::sText('partners_amount', $model),
                AdminColumn::sText('used_partners_amount', $model),
                AdminColumn::sText('canceled_partners_amount', $model),
                AdminColumn::sText('partners_balance_after', $model),
                AdminColumn::sText('created_at', $model)->setWidth('150px'),

                /*Default Currency*/
                AdminColumn::sLink('id', '#')->setWidth('30px'),
                AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true),
                AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
                AdminColumn::sText('default_balance_before', $model),
                AdminColumn::sText('deposits_default_amount', $model),
                AdminColumn::sText('baccarat_bets_default_amount', $model),
                AdminColumn::sText('roulette_bets_default_amount', $model),
                AdminColumn::sText('used_bonuses_default_amount', $model),
                AdminColumn::sText('used_partners_default_amount', $model),
                AdminColumn::sText('baccarat_bets_default_outcome', $model),
                AdminColumn::sText('roulette_bets_default_outcome', $model),
                AdminColumn::sText('withdrawals_default_amount', $model),
                AdminColumn::sText('default_balance_after', $model),
                AdminColumn::sText('created_at', $model)->setWidth('150px'),
                AdminColumn::sText('bets_wins_default_alteration', $model),
                AdminColumn::sText('default_balance_alteration', $model),

                AdminColumn::sLink('id', '#')->setWidth('30px'),
                AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true),
                AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
                AdminColumn::sText('bonuses_default_balance_before', $model),
                AdminColumn::sText('bonuses_default_amount', $model),
                AdminColumn::sText('used_bonuses_default_amount', $model),
                AdminColumn::sText('canceled_bonuses_default_amount', $model),
                AdminColumn::sText('bonuses_default_balance_after', $model),
                AdminColumn::sText('created_at', $model)->setWidth('150px'),

                AdminColumn::sLink('id', '#')->setWidth('30px'),
                AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true),
                AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
                AdminColumn::sText('partners_default_balance_before', $model),
                AdminColumn::sText('partners_default_amount', $model),
                AdminColumn::sText('used_partners_default_amount', $model),
                AdminColumn::sText('canceled_partners_default_amount', $model),
                AdminColumn::sText('partners_default_balance_after', $model),
                AdminColumn::sText('created_at', $model)->setWidth('150px'),
            ]);

            $display->setColumnsTotal(array_fill(0, $display->getColumns()->all()->count(), '-'));
            $display->getColumnsTotal()->setPlacement('table.footer');
        }

        return $display;
    }
}
