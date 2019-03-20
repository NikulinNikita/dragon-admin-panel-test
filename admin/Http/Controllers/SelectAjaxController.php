<?php

namespace Admin\Http\Controllers;

use App\Models\BaseModel;
use Illuminate\Http\Request;

class SelectAjaxController extends Controller
{
    public function getCustomAjaxSelectOptions(Request $request)
    {
        [$search, $relatedTable, $field] = array_values($request->all());
        $relatedModel          = str_singular($relatedTable);
        $model                 = "App\\Models\\" . ucwords(camel_case($relatedModel));
        $query                 = app()->make($model);
        $textRowTableWithField = "{$relatedTable}.{$field}";

        if (strpos($field, 'translations.') !== false) {
            $query                 = $query->join("{$relatedModel}_translations", "{$relatedModel}_translations.{$relatedModel}_id", '=', "{$relatedTable}.id")
                                           ->where("{$relatedModel}_translations.locale", app()->getLocale());
            $textRowTableWithField = "{$relatedModel}_{$field}";
        }

        $options = $query->where($textRowTableWithField, 'like', '%' . $search . '%')
                         ->selectRaw("{$relatedTable}.id AS `id`")->selectRaw("{$textRowTableWithField} AS `text`")
                         ->orderBy('id')->take(10)->get()->toArray();

        $options = BaseModel::stripArrayTags($options);

        return ['items' => $options];
    }

    public function getSelectAjaxOptions(Request $request)
    {
        ["q" => $search, "model" => $model, "field" => $field, "search" => $searchParameter, "relations" => $relations] = $request->all();
        ["isNullable" => $isNullable, "orderBy" => $orderBy, "limit" => $limit] = $request->all();
        $relatedModel          = \BaseModel::getDefaultSnakeCase($model);
        $relatedTable          = str_plural($relatedModel);
        $query                 = app()->make($model)->select("{$relatedTable}.*");
        $searchField           = $searchParameter ? $searchParameter : $field;
        $textRowTableWithField = "{$relatedTable}.{$searchField}";
        $limit                 = $limit != 0 ? $limit : null;
        $queryFilters          = $request->get('queryFilters') ? json_decode($request->get('queryFilters')) : null;
        $with                  = $request->get('with') ? json_decode($request->get('with')) : null;
        $joins                 = $request->get('joins') ? json_decode($request->get('joins')) : null;
        $selectRaws            = $request->get('selectRaws') ? json_decode($request->get('selectRaws'), true) : null;
        $distinct              = $request->get('distinct');
        $cte                   = $request->get('cte');

        $query = $this->useJoins($query, $joins, $relatedTable);
        $query = $this->useSelectRaws($query, $selectRaws);
        $query = $this->useQueryFilters($query, $queryFilters, $relatedTable);
        [$query, $textRowTableWithField] = $this->useTranslations($query, $textRowTableWithField, $relations, $relatedTable, $relatedModel);
        $query = $this->useDistinct($query, $distinct);
        $query = $this->useSearch($query, $search, $selectRaws, $field, $textRowTableWithField, $searchParameter, $cte, $orderBy, $limit);

        if ($with) {
            $options = is_array($with) ? $query->with($with) : $query;
            $options = $options->orderBy($orderBy)->take($limit)->distinct()->get();

            $items = array_pluck($options->all(), $field, 'id');
            $items = array_filter($items, function ($v, $k) use ($search) {
                return str_replace(' ', '', $search) ? strpos($v, $search) !== false : true;
            }, ARRAY_FILTER_USE_BOTH);

            $options = [];
            foreach ($items as $id => $item) {
                array_push($options, ['id' => $id, 'tag_name' => $item, 'custom_name' => null]);
            }
        } elseif ($cte) {
            $options = $query;
        } else {
            $options = $query->selectRaw("{$textRowTableWithField} AS `tag_name`");
            $options = $options->orderBy($orderBy)->take($limit)->get();
            $options = $options->toArray();
        }

        $options = BaseModel::stripArrayTags($options);

        return $isNullable ?
            array_prepend($options, ['id' => false, 'tag_name' => trans('sleeping_owl::lang.select.nothing'), 'custom_name' => null]) : $options;
    }

    protected function useJoins($query, $joins, $parentRelatedTable)
    {
        if ($joins && count($joins)) {
            foreach ($joins as $joinBlocks) {
                $joinSegments = explode('.', $joinBlocks);
                foreach ($joinSegments as $k => $joinParams) {
                    if ($k > 0) {
                        $segmentsJoinParams = $joinSegments[$k - 1];
                        [$joinType, $joinTable] = $this->getJoinTableAndType($segmentsJoinParams);
                        $relatedTable = $joinTable;
                    } else {
                        $relatedTable = $parentRelatedTable;
                    }

                    if (is_string($joinParams)) {
                        [$joinType, $joinTable] = $this->getJoinTableAndType($joinParams);
                        $joinModel    = str_singular($joinTable);
                        $relatedModel = str_singular($relatedTable);

                        if (isset($joinType)) {
                            if ($joinType === 'belongsToMany') {
                                $pivotTable = "{$joinModel}_{$relatedModel}";

                                $query = $query->leftJoin($pivotTable, "{$pivotTable}.{$relatedModel}_id", '=', "{$relatedTable}.id");
                                $query = $query->leftJoin($joinTable, "{$joinTable}.id", '=', "{$pivotTable}.{$joinModel}_id");
                            } elseif ($joinType === 'hasMany') {
                                $query = $query->leftJoin($joinTable, "{$joinTable}.{$relatedModel}_id", '=', "{$relatedTable}.id");
                            }
                        } else {
                            $query = $query->leftJoin($joinTable, "{$joinTable}.id", '=', "{$relatedTable}.{$joinModel}_id");
                        }
                    } else {
                        if (count($joinParams) === 5) {
                            [$joinRelatedTable, $joinCurrentTableToRelatedTableId, $joinSign, $joinRelatedTableId, $joinType] = $joinParams;
                        } else {
                            [$joinRelatedTable, $joinCurrentTableToRelatedTableId, $joinSign, $joinRelatedTableId] = $joinParams;
                        }
                        $joinType = $joinType ?? null;

                        if (in_array($joinType, ['left', 'leftJoin'])) {
                            $query = $query->leftJoin($joinRelatedTable, $joinCurrentTableToRelatedTableId, $joinSign, $joinRelatedTableId);
                        } else {
                            $query = $query->join($joinRelatedTable, $joinCurrentTableToRelatedTableId, $joinSign, $joinRelatedTableId);
                        }
                    }
                }
            }
        }

        return $query;
    }

    protected function getJoinTableAndType($joinParams)
    {
        $joinParams = explode('->', $joinParams);
        if (count($joinParams) > 1) {
            [$joinType, $joinTable] = $joinParams;
        } else {
            [$joinTable] = $joinParams;
        }

        return [$joinType ?? null, $joinTable];
    }

    protected function useSelectRaws($query, $selectRaws)
    {
        if ($selectRaws && count($selectRaws)) {
            foreach ($selectRaws as $asName => $selectRawString) {
                $asName = is_string($asName) ? $asName : 'custom_name';
                $query = $query->selectRaw("CONCAT({$selectRawString})  AS `{$asName}`");
            }
        }

        return $query;
    }

    protected function useQueryFilters($query, $queryFilters, $relatedTable)
    {
        if ($queryFilters && count($queryFilters)) {
            foreach ($queryFilters as $queryFilter) {
                if (count($queryFilter) === 2) {
                    [$key, $value] = $queryFilter;
                    $sign = '=';
                } else {
                    [$key, $sign, $value] = $queryFilter;
                }

                if (strpos($key, '_') === 0) {
                    $key = preg_replace('/_/', '', $key, 1);
                    ['relations' => $relations, 'key' => $key] = BaseModel::separateRelationsAndKey($key);

                    $query->whereHas($relations, function ($q) use ($key, $sign, $value) {
                        if (is_array($value)) {
                            $q->whereIn($key, $value);
                        } else {
                            $q->where($key, $sign, $value);
                        }
                    });
                } else {
                    if (is_array($value)) {
                        $query = $query->whereIn($key, $value);
                    } else {
                        $query = $query->where($key, $sign, $value);
                    }
                }
            }
        }

        return $query;
    }

    protected function useTranslations($query, $textRowTableWithField, $relations, $relatedTable, $relatedModel)
    {
        if (strpos($relations, 'translations.') !== false) {
            $query = $query->join("{$relatedModel}_translations", "{$relatedModel}_translations.{$relatedModel}_id", '=', "{$relatedTable}.id")
                           ->where("{$relatedModel}_translations.locale", app()->getLocale());

            $textRowTableWithField = "{$relatedModel}_{$relations}";
        }

        return [$query, $textRowTableWithField];
    }

    protected function useDistinct($query, $distinct = false)
    {
        return $distinct ? $query->distinct() : $query;
    }

    protected function useSearch($query, $search, $selectRaws, $field, $textRowTableWithField, $searchParameter, $cte, $orderBy, $limit)
    {
        if ($cte) {
            $groupedData  = $query;
            $groupedData1 = \DB::raw("WITH `cte1` as (" . $groupedData->toSql() . ")");
            $groupedData2 = \DB::table('cte1')->whereRaw("cte1.custom_name LIKE '%{$search}%'")->orderBy($orderBy)->take($limit);
            $query        = \DB::select("{$groupedData1->getValue()} {$groupedData2->toSql()}", $groupedData->getBindings());
        } elseif ($selectRaws) {
            $selectRawString = array_get($selectRaws, 'custom_name') ?? array_first($selectRaws);
            $query = $query->whereRaw("CONCAT({$selectRawString}) LIKE '%{$search}%'");
        } elseif (str_replace(' ', '', $search) && (strpos($field, 'Admin') !== 0 || (strpos($field, 'Admin') === 0 && $searchParameter !== $field))) {
            $query = $query->where($textRowTableWithField, 'like', "%{$search}%");
        }

        return $query;
    }
}
