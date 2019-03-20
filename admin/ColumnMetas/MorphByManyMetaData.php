<?php

namespace Admin\ColumnMetas;

use Illuminate\Database\Eloquent\Builder;
use SleepingOwl\Admin\Contracts\Display\ColumnMetaInterface;
use SleepingOwl\Admin\Contracts\Display\NamedColumnInterface;

class MorphByManyMetaData extends BaseMetaData implements ColumnMetaInterface
{
    public function onFilterSearch(NamedColumnInterface $column, Builder $query, $queryString, $queryParams)
    {
        if ( ! $queryString) {
            return $query;
        }

        $separatedColumn = snake_case(array_first(explode('.', $column->getName())));
        [$morphType, $relatedItem] = explode('_', $separatedColumn, 2);
        $type = str_singular($relatedItem);

        foreach (is_array($queryString) ? $queryString : [$queryString] as $k => $id) {
            if ( ! $k) {
                $query->whereHas($relatedItem, function ($query) use ($relatedItem, $type, $id, $morphType) {
                    return $query->where([["{$morphType}_type", $type], ["{$morphType}_id", $id]]);
                });
            } else {
                $query->orWhereHas($relatedItem, function ($query) use ($relatedItem, $type, $id, $morphType) {
                    return $query->where([["{$morphType}_type", $type], ["{$morphType}_id", $id]]);
                });
            }
        }

        return $query;
    }
}
