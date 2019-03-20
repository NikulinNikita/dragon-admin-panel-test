<?php

namespace Admin\ColumnMetas;

use BaseModel;
use Illuminate\Database\Eloquent\Builder;
use SleepingOwl\Admin\Contracts\Display\ColumnMetaInterface;
use SleepingOwl\Admin\Contracts\Display\NamedColumnInterface;

class DateTimeMetaData extends BaseMetaData implements ColumnMetaInterface
{
    public function onFilterSearch(NamedColumnInterface $column, Builder $query, $queryString, $queryParams)
    {
        if ( ! $queryString) {
            return $query;
        }

        [$columnName, $model, $table] = BaseModel::getColumnAndModelAndTable($column->getName(), $query);
        [$from, $to] = explode('::', $queryParams['value']);

        if ($from === '') {
            $query->where("{$table}.{$columnName}", '<=', $to);
        } else if ($to === '') {
            $query->where("{$table}.{$columnName}", '>=', $from);
        } else {
            $query->whereBetween("{$table}.{$columnName}", [$from, $to]);
        }

        return $query;
    }
}
