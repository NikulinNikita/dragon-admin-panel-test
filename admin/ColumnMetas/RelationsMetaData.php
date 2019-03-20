<?php

namespace Admin\ColumnMetas;

use BaseModel;
use Illuminate\Database\Eloquent\Builder;
use SleepingOwl\Admin\Contracts\Display\ColumnMetaInterface;
use SleepingOwl\Admin\Contracts\Display\NamedColumnInterface;

class RelationsMetaData extends BaseMetaData implements ColumnMetaInterface
{
    public function onFilterSearch(NamedColumnInterface $column, Builder $query, $queryString, $queryParams)
    {
        if ( ! $queryString) {
            return $query;
        }

        $relationsWithColumnName = $column->getName();
        $relations               = substr($relationsWithColumnName, 0, strrpos($relationsWithColumnName, '.'));

        $query->whereHas($relations, function ($q) use ($queryString) {
            $relationsTable = $q->getModel()->getTable();
            $q->whereIn("{$relationsTable}.id", is_array($queryString) ? $queryString : [$queryString]);
        });

        return $query;
    }

    public function onOrderBy(NamedColumnInterface $column, Builder $query, $direction)
    {
        [$columnName, $model, $table] = BaseModel::getColumnAndModelAndTable($column->getName(), $query);

        return $query->OrderByRelations($columnName, $direction);
    }
}
