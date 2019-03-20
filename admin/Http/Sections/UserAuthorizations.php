<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\DateTimeMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnEditable;
use AdminColumnFilter;
use AdminDisplay;
use AdminDisplayFilter;
use App\Models\User;
use Carbon\Carbon;

class UserAuthorizations extends BaseSection
{
    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();;

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'user_id')) {
                $query->where("{$model->getTable()}.user_id", array_get($scopes, 'user_id'));
            }
        });
        $display->setParameters(['user_id' => array_get($scopes, 'user_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['user']);

        $display->setFilters(
            request()->get('userIds') ?
                AdminDisplayFilter::field('user_id')->setAlias('userIds')->setOperator('in')->setTitle(function ($value) {
                    $result = implode(', ', $value);

                    return "User IDs: [{$result}]";
                }) : AdminDisplayFilter::field('xxx'),
            AdminDisplayFilter::custom('date_from')->setCallback(function ($query, $value) {
                $query->where('created_at', '>=', Carbon::parse($value));
            })->setTitle('Date From: [:value]'),
            AdminDisplayFilter::custom('date_to')->setCallback(function ($query, $value) {
                $query->where('created_at', '<=', Carbon::parse($value)->addDay()->subSecond());
            })->setTitle('Date To: [:value]')
        );

        if ( ! array_get($scopes, 'noFilters')) {
            $columnFilters = [
                null,
                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilter::sSelect()->setEnum(config('selectOptions.authorizations.source'))->multiple(),
                AdminColumnFilterComponent::rangeDate(),
            ];
            if ( ! $scopes) {
                array_splice($columnFilters, 1, 0, [
                    AdminColumnFilter::sSelect(User::class, 'name')->setColumnName('user_id')->multiple(),
                ]);
            }
            $display->setColumnFilters($columnFilters)->setPlacement('table.header');
        }

        $columns = [
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('ip', $model),
            AdminColumnEditable::sSelect('source', $model)->setEnum(config('selectOptions.authorizations.source')),
            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
        ];

        if ( ! $scopes) {
            array_splice($columns, 1, 0, [
                AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true),
            ]);
        } else {
//            $display->getColumns()->disableControls();
        }

        return $display->setColumns($columns);
    }
}
