<?php

namespace Admin\ColumnMetas;

use BaseModel;
use Illuminate\Database\Eloquent\Builder;
use SleepingOwl\Admin\Contracts\Display\ColumnMetaInterface;
use SleepingOwl\Admin\Contracts\Display\NamedColumnInterface;

class BaseMetaData implements ColumnMetaInterface
{
    public function onFilterSearch(NamedColumnInterface $column, Builder $query, $queryString, $queryParams)
    {
        if ( ! $queryString) {
            return $query;
        }

        [$columnName, $model, $table] = BaseModel::getColumnAndModelAndTable($column->getName(), $query);

        $query->whereIn("{$table}.{$columnName}", is_array($queryString) ? $queryString : [$queryString]);

        return $query;
    }

    public function onOrderBy(NamedColumnInterface $column, Builder $query, $direction)
    {
        [$columnName, $model, $table] = BaseModel::getColumnAndModelAndTable($column->getName(), $query);

        return $query->OrderBy("{$table}.{$columnName}", $direction);
    }

    public function onSearch(NamedColumnInterface $column, Builder $query, $search)
    {
        $query->orWhereHas('translations', function ($q) use ($column, $search) {
            return $q->where($column->getName(), 'like', "%$search%");
        });
    }
}
