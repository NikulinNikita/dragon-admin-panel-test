<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\DateTimeMetaData;
use Admin\ColumnMetas\RelationsMetaData;
use Admin\ColumnMetas\RelationsWithTranslationMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminDisplayFilter;
use App\Models\BaseModel;
use App\Models\Staff;
use App\Models\Table;
use App\Models\TableLimit;
use App\Models\User;
use Carbon\Carbon;

class UserSessions extends BaseSection
{
    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'user_id')) {
                $query->where("{$model->getTable()}.user_id", array_get($scopes, 'user_id'));
            }
        });
        $display->setParameters(['user_id' => array_get($scopes, 'user_id')]);
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['user', 'tableLimit', 'staffSessions.staff', 'table']);

        $display->setFilters(
            AdminDisplayFilter::custom('staff_session_id')->setCallback(function ($query, $value) {
                $query->whereHas('staffSessions', function ($q) use ($value) {
                    return $q->where('staff_sessions.id', $value);
                });
            })->setTitle('Staff Session ID: [:value]'),
            AdminDisplayFilter::custom('staff')->setCallback(function ($query, $value) {
                $query->whereHas('staffSessions.staff', function ($q) use ($value) {
                    return $q->where('name', $value);
                });
            })->setTitle('Staff: [:value]'),
            AdminDisplayFilter::custom('table')->setCallback(function ($query, $value) {
                $query->whereHas('table', function ($q) use ($value) {
                    return $q->where('slug', $value);
                });
            })->setTitle('Table: [:value]'),
            AdminDisplayFilter::custom('date_from')->setCallback(function ($query, $value) {
                $query->where('created_at', '>=', Carbon::parse($value));
            })->setTitle('Date From: [:value]'),
            AdminDisplayFilter::custom('date_to')->setCallback(function ($query, $value) {
                $query->where('created_at', '<=', request()->get('dateTime') ? Carbon::parse($value) : Carbon::parse($value)->addDay()->subSecond());
            })->setTitle('Date To: [:value]')
        );

        if ( ! array_get($scopes, 'noFilters')) {
            $columnFilters = [
                null,
                AdminColumnFilter::sSelect(Staff::class, 'name')->setColumnName('staffSessions.staff_id')->multiple()
                                 ->setJoins(['belongsToMany->roles'])->setQueryFilters([['roles.name', 'dealer']]),
                AdminColumnFilter::sSelect(TableLimit::class, 'title')->setColumnName('table_limit_id')->multiple(),
                AdminColumnFilter::sSelect(Table::class, 'translations.title')->setColumnName('table_limit_id')->multiple(),
                AdminColumnFilter::text()->setOperator('contains'),
                AdminColumnFilter::text()->setOperator('contains'),
//                AdminColumnFilterComponent::rangeInput(),
                null,
                AdminColumnFilter::sSelect()->setEnum(config('selectOptions.common.status'))->multiple(),
                AdminColumnFilterComponent::rangeDate(),
                AdminColumnFilterComponent::rangeDate(),
            ];
            if ( ! $scopes) {
                array_splice($columnFilters, 1, 0, [
                    AdminColumnFilter::sSelect(User::class, 'name')->setColumnName('user.name')->multiple(),
                ]);
            }
            $display->setColumnFilters($columnFilters)->setPlacement('table.header');
        }

        $columns = [
            AdminColumn::link('id', '#')->setWidth('30px'),
            AdminColumn::sCustom('Staff', $model, function (BaseModel $model) {
                $result = '';
                foreach ($model->staffSessions as $staffSession) {
                    $result = $result . "<li><span class=\"label label-info\">{$staffSession->staff->name}</span></li>";
                }

                return "<ul style='padding: 0;'>{$result}</ul>";
            })->setHtmlAttribute('class', 'custom-list-items')->setWidth('150px')->setShowTags(true),
            AdminColumn::sRelatedLink('tableLimit.title', $model)->setOrderable(true)->setMetaData(RelationsMetaData::class),
            AdminColumn::sRelatedLink('table.title', $model)->setOrderable(true)->setMetaData(RelationsWithTranslationMetaData::class),
            AdminColumn::sText('subtable', $model),
            AdminColumn::sText('seat', $model),
//            AdminColumn::sText('bets', $model),
            AdminColumn::sCustom('duration', $model, function ($model) {
                return $model->duration ? round($model->duration / 60) : null;
            })->setOrderable(true),
            AdminColumn::sText('status', $model)->setMetaData(BaseMetaData::class),
            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
            AdminColumn::sText('ended_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
        ];
        if ( ! $scopes) {
            array_splice($columns, 1, 0, [
                AdminColumn::sRelatedLink('user.name', $model)->setOrderable(true)->setMetaData(RelationsMetaData::class),
            ]);
        } else {
            $this->canNotCreate = true;
        }

        return $display->setColumns($columns);
    }
}
