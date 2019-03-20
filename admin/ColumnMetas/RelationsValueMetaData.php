<?php

namespace Admin\ColumnMetas;

use Illuminate\Database\Eloquent\Builder;
use SleepingOwl\Admin\Contracts\Display\NamedColumnInterface;

class RelationsValueMetaData extends RelationsMetaData
{
    public function onFilterSearch(NamedColumnInterface $column, Builder $query, $queryString, $queryParams)
    {
        if ( ! $queryString) {
            return $query;
        }

        $relationsWithColumnName = $column->getName();
        $relations               = substr($relationsWithColumnName, 0, strrpos($relationsWithColumnName, '.'));
        $column                  = last(explode('.', $relationsWithColumnName));

        $query->whereHas($relations, function ($q) use ($queryString, $column) {
            $q->whereIn($column, is_array($queryString) ? $queryString : [$queryString]);
        });

        return $query;
    }
}
