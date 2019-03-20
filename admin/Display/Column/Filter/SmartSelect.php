<?php

namespace Admin\Display\Column\Filter;

use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Display\Column\Filter\Select;

class SmartSelect extends Select
{
    protected $view = 'admin::column.filter.customAjaxSelect';
    protected $search;
    protected $search_url;
    protected $displayWithRelations = '';
    protected $isDisabled = false;
    protected $makeNullable = false;
    protected $min_symbols = 0;
    protected $staticOptions = false;
    protected $queryFilters = null;
    protected $with = null;
    protected $orderBy = 'id';
    protected $queryLimit = 10;
    protected $joins;
    protected $selectRaws;
    protected $distinct;
    protected $cte;

    public function __construct($options = null, $title = null)
    {
        parent::__construct($options, $title);

        if (is_array($options)) {
            $this->staticOptions = true;
            $this->setOptions($options);
        } elseif (($options instanceof Model) || is_string($options)) {
            $this->setModelForOptions($options);
        }

        if ( ! is_null($title)) {
            $this->setDisplay($title);
        }
    }

    public function initialize()
    {
        parent::initialize();

        $this->setHtmlAttributes([
            'class'            => 'js-data-smartSelect',
            'model'            => ! $this->isStaticOptions() ? get_class($this->getModelForOptions()) : null,
            'field'            => $this->getDisplay(),
            'placeholderText'  => $this->getPlaceholder() ?? '',
            'relations'        => $this->displayWithRelations,
            'search'           => $this->getSearch(),
            'search_url'       => $this->getSearchUrl(),
            'data-min-symbols' => $this->getMinSymbols(),
            'isReadOnly'       => $this->isDisabled,
            'isNullable'       => $this->makeNullable,
            'isStaticOptions'  => $this->isStaticOptions(),
            'queryFilters'     => json_encode($this->getQueryFilters()),
            'with'             => json_encode($this->getWith()),
            'orderBy'          => $this->getOrderBy(),
            'limit'            => $this->getLimit(),
            'joins'            => $this->getJoins(),
            'selectRaws'       => $this->getSelectRaws(),
            'distinct'          => $this->getDistinct(),
            'cte'               => $this->getCte(),
        ]);
    }

    public function setDisplay($display)
    {
        $this->display              = str_replace('translations.', '', $display);
        $this->displayWithRelations = $display;

        return $this;
    }

    public function getCte()
    {
        return $this->cte;
    }

    public function setCte($value)
    {
        $this->cte = $value ? 1 : 0;

        return $this;
    }

    public function getDistinct()
    {
        return $this->distinct;
    }

    public function setDistinct($distinct)
    {
        $this->distinct = $distinct ? 1 : 0;

        return $this;
    }

    public function getSelectRaws()
    {
        return $this->selectRaws;
    }

    public function setSelectRaws($value)
    {
        $this->selectRaws = json_encode($value);

        return $this;
    }

    public function getJoins()
    {
        return $this->joins;
    }

    public function setJoins($value)
    {
        $this->joins = json_encode($value);

        return $this;
    }

    public function getMinSymbols()
    {
        return $this->min_symbols;
    }

    public function setMinSymbols($symbols)
    {
        $this->min_symbols = $symbols;

        return $this;
    }

    public function isStaticOptions()
    {
        return $this->staticOptions;
    }

    public function setEnum(array $values)
    {
        $this->staticOptions = true;

        return $this->setOptions(array_combine($values, $values));
    }

    public function getSearch()
    {
        if ($this->search) {
            return $this->search;
        }

        return $this->getDisplay();
    }

    public function setSearch($search)
    {
        $this->search = $search;

        return $this;
    }

    public function getSearchUrl()
    {
        return $this->search_url ? $this->search_url : route('admin.getSelectAjaxOptions');
    }


    public function getQueryFilters()
    {
        return $this->queryFilters;
    }

    public function setQueryFilters($value)
    {
        $this->queryFilters = $value;

        return $this;
    }

    public function getWith()
    {
        return $this->with;
    }

    public function setWith($value)
    {
        $this->with = $value;

        return $this;
    }

    public function getOrderBy()
    {
        return $this->orderBy;
    }

    public function setOrderBy($value)
    {
        $this->orderBy = $value;

        return $this;
    }

    public function getLimit()
    {
        return $this->queryLimit;
    }

    public function setLimit($value)
    {
        $this->queryLimit = $value;

        return $this;
    }

    protected function loadOptions()
    {
        return [];
    }
}
