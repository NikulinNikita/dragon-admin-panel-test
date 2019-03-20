<?php

namespace Admin\ColumnMetas;

use Illuminate\Database\Eloquent\Builder;
use SleepingOwl\Admin\Contracts\Display\ColumnMetaInterface;
use SleepingOwl\Admin\Contracts\Display\NamedColumnInterface;

class RelationsWithTranslationMetaData extends RelationsMetaData implements ColumnMetaInterface
{
    public function onOrderBy(NamedColumnInterface $column, Builder $query, $direction)
    {
        return $query->OrderByRelations($column->getName(), $direction, $withTranslation = true);
    }
}
