<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\AdminGetterInputMetaData;
use Admin\ColumnMetas\DateTimeMetaData;
use Admin\ColumnMetas\RelationsMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use App\Models\BaccaratResult;
use App\Models\Staff;
use App\Models\User;
use BaseModel;

class ReportsBaccaratBets extends BaseSection
{
    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[9, 'desc']]);
        $display->with(['user', 'result', 'baccaratRound.staffSession.staff']);

        if ( ! array_get($scopes, 'noFilters')) {
            $alias      = $this->getAlias();
            $buttonType = ["export", "reGenerateReport"];
            $display->getActions()->setView(view('admin::datatables.toolbar', compact('alias', 'buttonType')))->setPlacement('panel.heading.actions');

            $display->setColumnFilters([
                null,
                AdminColumnFilter::sSelect(Staff::class, 'name')->setColumnName('baccaratRound.staffSession.staff.name')->multiple()
                                 ->setJoins(['belongsToMany->roles'])->setQueryFilters([['roles.name', 'dealer']]),
                AdminColumnFilter::sSelect(User::class, 'name')->setColumnName('user_id')->multiple(),
                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilter::sSelect(BaccaratResult::class, 'code')->setColumnName('baccarat_result_id')->multiple(),
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
            AdminColumn::sRelatedLink('baccaratRound.staffSession.staff.name', $model)->setOrderable(true)->setMetaData(RelationsMetaData::class),
            AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true),
            AdminColumn::sRelatedLink('baccaratRound.id', $model)->setOrderable(true),
            AdminColumn::sRelatedLink('result.code', $model)->setOrderable(true),
            AdminColumn::sText('AdminAmount', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('AdminOutcome', $model)->setMetaData(AdminGetterInputMetaData::class),
            $exportReport ? AdminColumn::sText('default_amount', $model) :
                AdminColumn::sText('AdminDefaultAmount', $model)->setMetaData(AdminGetterInputMetaData::class),
            $exportReport ? AdminColumn::sText('default_outcome', $model) :
                AdminColumn::sText('AdminDefaultOutcome', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
        ]);

        if ($exportReport) {
            $display->setColumns([
                AdminColumn::sText('player', $model),
                AdminColumn::sText('banker', $model),
                AdminColumn::sText('tie', $model),
                AdminColumn::sText('player-pair', $model),
                AdminColumn::sText('banker-pair', $model),
                AdminColumn::sText('big', $model),
                AdminColumn::sText('small', $model),
                AdminColumn::sText('player-dragon', $model),
                AdminColumn::sText('banker-dragon', $model),
            ]);

            $totalQuery = $this->getModel()->query();
            $display->getFilters()->initialize();
            $display->getFilters()->modifyQuery($totalQuery);
            $result = $totalQuery
                ->selectRaw("SUM(default_amount) AS `default_amount`")
                ->selectRaw("SUM(default_outcome) AS `default_outcome`")
                ->first();
            $display->setColumnsTotal([
                '<b>' . trans("admin/common.Total") . ':</b>',
                null,
                null,
                null,
                null,
                null,
                '<b>' . BaseModel::exchangeCurrency($result->default_amount) . '</b>',
                null,
                '<b>' . BaseModel::exchangeCurrency($result->default_amount) . '</b>',
            ],
                $display->getColumns()->all()->count()
            );
            $display->getColumnsTotal()->setPlacement('table.footer');
        }

        return $display;
    }
}
