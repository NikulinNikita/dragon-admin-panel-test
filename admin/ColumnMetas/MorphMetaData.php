<?php

namespace Admin\ColumnMetas;

use BaseModel;
use Illuminate\Database\Eloquent\Builder;
use SleepingOwl\Admin\Contracts\Display\ColumnMetaInterface;
use SleepingOwl\Admin\Contracts\Display\NamedColumnInterface;

class MorphMetaData extends BaseMetaData implements ColumnMetaInterface
{
    public function onFilterSearch(NamedColumnInterface $column, Builder $query, $queryString, $queryParams)
    {
        if ( ! $queryString) {
            return $query;
        }

        $relatedItem = snake_case(array_first(explode('.', $column->getName())));
        $rawTypes    = [];
        $ids         = [];
        $types       = [];
        foreach (is_array($queryString) ? $queryString : [$queryString] as $k => $val) {
            list($rawTypes[], $ids[]) = explode('-', $val);
            $types[] = "App\\Models\\{$rawTypes[$k]}";
        }

        foreach ($types as $k => $type) {
            if ( ! $k) {
                $query->where(function ($q) use ($relatedItem, $type, $ids, $rawTypes, $k) {
                    $q->where([["{$relatedItem}_type", $type], ["{$relatedItem}_id", $ids[$k]]])
                      ->orWhere([["{$relatedItem}_type", $rawTypes[$k]], ["{$relatedItem}_id", $ids[$k]]]);
                });
            } else {
                $query->orWhere(function ($q) use ($relatedItem, $type, $ids, $rawTypes, $k) {
                    $q->where([["{$relatedItem}_type", $type], ["{$relatedItem}_id", $ids[$k]]])
                      ->orWhere([["{$relatedItem}_type", $rawTypes[$k]], ["{$relatedItem}_id", $ids[$k]]]);
                });
            }
        }

        return $query;
    }

    public function onOrderBy(NamedColumnInterface $column, Builder $query, $direction)
    {
        $obj         = $query->first();
        $model       = BaseModel::getDefaultSnakeCase(get_class($obj));
        $relation    = array_first(explode('.', $column->getName()));
        $morphType   = "{$relation}_type";
        $relatedItem = snake_case(class_basename($obj->{$morphType}));

        $query->OrderByMorph($column->getName(), $direction, $model, $relatedItem);
    }
}
