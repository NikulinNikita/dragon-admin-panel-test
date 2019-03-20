<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\AdminGetterInputMetaData;
use Admin\ColumnMetas\MorphMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use App\Models\Staff;

class Operations extends BaseSection
{
    const TYPE_MAP_CLASS = [
        'baccarat_bet' => \App\Models\BaccaratBet::class,
        'roulette_bet' => \App\Models\RouletteBet::class
    ];
    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $amountType          = 'AdminAmount';
        $isWithdrawalRequest = array_key_exists('withdrawal_request', $scopes)
                               && $scopes['withdrawal_request'];

        if ($isWithdrawalRequest) {
            $amountType = 'AbsAmount';
        }

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'user_till_id')) {
                $query->where("{$model->getTable()}.user_till_id", array_get($scopes, 'user_till_id'));
            }

            if (array_key_exists('types', $scopes)) {
                $query->where('amount', '<', 0);
                $query->whereIn('operatable_type', $scopes['types']);
            }

            if (array_key_exists('user_till_id', $scopes)) {
                $query->where('user_till_id', $scopes['user_till_id']);
            }

            if (array_key_exists('last_deposit', $scopes) && array_key_exists('request_date', $scopes)) {
                $query->whereBetween('created_at', [$scopes['last_deposit']['date'], $scopes['request_date']['date']]);
            }

            if (array_key_exists('has_opposite_bets', $scopes)) {
                $query->where(function ($q1) use ($scopes) {
                    $q1->whereHas('morphBaccaratBets', function ($query) use ($scopes) {
                        $query->where('baccarat_bets.has_opposite_bets', array_get($scopes, 'has_opposite_bets'));
                    })->orWhereHas('morphRouletteBets', function ($query) use ($scopes) {
                        $query->where('roulette_bets.has_opposite_bets', array_get($scopes, 'has_opposite_bets'));
                    });
                });
            }
        });
        $display->setParameters(['user_till_id' => array_get($scopes, 'user_till_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['operatable', 'userTill', 'staff']);

//        $model        = $this->getAlias();
//        $requestQuery = request()->query();
//        $buttonType   = "export";
//        $display->getActions()->setView(view('admin::datatables.toolbar',
//            compact('model', 'requestQuery', 'buttonType')))->setPlacement('panel.heading.actions');

        if ( ! array_get($scopes, 'noFilters')) {
            $display->setColumnFilters([
                null,
                AdminColumnFilter::sSelect(Staff::class, 'name')->setColumnName('staff_id')->multiple(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilterComponent::rangeInput(),
                AdminColumnFilter::sSelect()->setEnum(config('selectOptions.operations.operatable_type'))->multiple(),
                null,
                AdminColumnFilterComponent::rangeDate(),
            ])->setPlacement('table.header');
        }

        $operatableColumn = ! $isWithdrawalRequest
            ? AdminColumn::sRelatedLink('operatable.id', $model)->setOrderable(! $isWithdrawalRequest)->setMetaData(MorphMetaData::class)
            : AdminColumn::sCustom('operatable.id', $model, function ($model) {
                if (array_key_exists($model->operatable_type, self::TYPE_MAP_CLASS)) {
                    $link = \AdminSection::getModel(self::TYPE_MAP_CLASS[$model->operatable_type])->getEditUrl($model->operatable->id);

                    return "<a href='{$link}'>{$model->operatable->id}</a>";
                }
            })->setShowTags(true);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('staff.name', $model)->setOrderable(true),
            AdminColumn::sText('AdminAmount', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('AdminBalance', $model)->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::sText('operatable_type', $model),
            AdminColumn::sText('created_at', $model)->setWidth('150px'),
            $operatableColumn
        ]);

        if ( ! $isWithdrawalRequest) {
            $display->setColumns([AdminColumn::text('AdminBalance', 'Balance')->setMetaData(AdminGetterInputMetaData::class),]);
        }

        /*$display->setColumns([
            AdminColumn::link('id', '#')->setWidth('30px'),
            AdminColumn::relatedLink('staff.name', 'Staff')->setOrderable(true),
            AdminColumn::text($amountType, 'Amount')->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::text('AdminBalance', 'Balance')->setMetaData(AdminGetterInputMetaData::class),
            AdminColumn::text('operatable_type', 'Type'),
            AdminColumn::relatedLink('operatable.id', 'Request ID')->setOrderable(true)->setMetaData(MorphMetaData::class),
            AdminColumn::text('created_at', 'Date')->setWidth('150px'),
        ]);*/

        if ( ! $scopes) {
            $display->setColumns([
                AdminColumn::sRelatedLink('userTill.id', $model)->setOrderable(true),
            ]);
        } else {
//            $this->canCreate = false;
        }

        return $display;
    }
}
