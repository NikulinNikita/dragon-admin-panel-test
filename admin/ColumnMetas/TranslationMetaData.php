<?php

namespace Admin\ColumnMetas;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use SleepingOwl\Admin\Contracts\Display\ColumnMetaInterface;
use SleepingOwl\Admin\Contracts\Display\NamedColumnInterface;

class TranslationMetaData extends BaseMetaData implements ColumnMetaInterface
{
    public function onFilterSearch(NamedColumnInterface $column, Builder $query, $queryString, $queryParams)
    {
        if ( ! $queryString) {
            return $query;
        }

        $query->whereHas('translations', function ($q) use ($column, $queryString) {
            return $q->where($column->getName(), 'like', "%$queryString%");
        });

        return $query;
    }

    public function onOrderBy(NamedColumnInterface $column, Builder $query, $direction)
    {
        [$columnName, $model, $table] = BaseModel::getColumnAndModelAndTable($column->getName(), $query);
        $relatedKey = $model === 'bank_account_currency' ? 'ba_currency' : null;

        $query->OrderByTranslations($columnName, $direction, $model, $relatedKey, $table);
    }
}
