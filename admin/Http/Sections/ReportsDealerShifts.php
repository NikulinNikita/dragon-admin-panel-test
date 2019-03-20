<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\AdminGetterInputMetaData;
use Admin\ColumnMetas\DateTimeMetaData;
use Admin\ColumnMetas\RelationsMetaData;
use Admin\ColumnMetas\RelationsWithTranslationMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnEditable;
use AdminColumnFilter;
use AdminDisplay;
use AdminDisplayFilter;
use App\Models\Staff;
use App\Models\Table;
use App\Models\UserSession;
use BaseModel;

class ReportsDealerShifts extends BaseSection
{
    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[11, 'desc']]);
        $display->with(['staffSession.staff', 'table']);

        if ( ! array_get($scopes, 'noFilters')) {
            $alias = $this->getAlias();
            $buttonType = ["export", "reGenerateReport"];
            $display->getActions()->setView(view('admin::datatables.toolbar', compact('alias', 'buttonType')))->setPlacement('panel.heading.actions');

            $display->setFilters(
                AdminDisplayFilter::field('bets_count')->setTitle('Bets Count: [:value]')
            );

            $display->setColumnFilters([
                null,
                AdminColumnFilter::sSelect(Staff::class, 'name')->setColumnName('staffSession.staff.name')->multiple(),
                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilter::sSelect(Table::class, 'translations.title')->setColumnName('table_id')->multiple(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                null,
                AdminColumnFilterComponent::rangeDate(),
            ])->setPlacement('table.header');
        }

        $exportReport = request()->get('includeHiddenColumns') || strpos(request()->url(), 'exportReport');
        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('staffSession.staff.name', $model)->setOrderable(true)->setMetaData(RelationsMetaData::class),
            AdminColumnEditable::sText('comment', $model),
            AdminColumnEditable::sText('manager', $model),
            AdminColumn::sRelatedLink('table.title', $model)->setOrderable(true)->setMetaData(RelationsWithTranslationMetaData::class),
            AdminColumn::sText('AdminUsersCount', $model)->setShowTags(true)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('AdminBetsCount', $model)->setShowTags(true)->setMetaData(AdminGetterInputMetaData::class),
            $exportReport ? AdminColumn::sText('bets_amount', $model) :
                AdminColumn::sText('AdminBetsAmountFormatted', $model)->setMetaData(AdminGetterInputMetaData::class),
            $exportReport ? AdminColumn::sText('balance', $model) :
                AdminColumn::sText('AdminBalanceFormatted', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('AdminProfitability', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('AdminPeriod', $model)->setWidth('100px')->setOrderable(false),
            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
        ]);

        if ($exportReport) {
            $totalQuery = $this->getModel()->query();
            $display->getFilters()->initialize();
            $display->getFilters()->modifyQuery($totalQuery);
            $result = $totalQuery
                ->selectRaw("SUM(users_count) AS `users_count`")
                ->selectRaw("SUM(bets_count) AS `bets_count`")
                ->selectRaw("SUM(bets_amount) AS `bets_amount`")
                ->selectRaw("SUM(balance) AS `balance`")
                ->first();
            $display->setColumnsTotal([
                '<b>' . trans("admin/common.Total") . ':</b>',
                null,
                null,
                null,
                null,
                '<b>' . $result->users_count . '</b>',
                '<b>' . $result->bets_count . '</b>',
                '<b>' . BaseModel::formatCurrency(1, $result->bets_amount) . '</b>',
                '<b>' . BaseModel::formatCurrency(1, $result->balance) . '</b>',
            ],
                $display->getColumns()->all()->count()
            );
            $display->getColumnsTotal()->setPlacement('table.footer');
        }

        return $display;
    }
}
