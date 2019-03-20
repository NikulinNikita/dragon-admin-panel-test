<?php

namespace Admin\Form\Element;

use Admin\Traits\AdminSmartComponentsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Contracts\Repositories\RepositoryInterface;
use SleepingOwl\Admin\Form\Element\Select;
use SleepingOwl\Admin\Form\Element\SelectAjax;

class SmartSelect extends SelectAjax
{
    use AdminSmartComponentsTrait;

    protected $view = 'admin::form.element.smartSelect';
    protected static $route = 'smartSelect';
    protected $placeholder;
    protected $displayWithRelations = '';
    protected $isDisabled = false;
    protected $makeNullable = false;
    protected $min_symbols = 0;
    protected $staticOptions = false;
    protected $queryFilters = null;
    protected $with = null;
    protected $templateResult;
    protected $templateSelection;
    protected $orderBy = 'id';
    protected $queryLimit = 10;
    protected $joins;
    protected $selectRaws;
    protected $distinct;
    protected $cte;

    public function __construct($path, $label = null, $options = [])
    {
        $label = $this->getSmartLabel($path, $label);
        parent::__construct($path, $label);

        if (is_array($options)) {
            $this->staticOptions = true;
            $this->setOptions($options);
        } elseif (($options instanceof Model) || is_string($options)) {
            $this->setModelForOptions($options);
        }

        $this->setLoadOptionsQueryPreparer(function ($item, Builder $query) {
            $repository = app(RepositoryInterface::class);
            $repository->setModel($this->getModelForOptions());
            $key = $repository->getModel()->getKeyName();

            return $query->where([$key => $this->getValueFromModel()]);
        });
    }

    public function setDisplay($display)
    {
        $this->display              = str_replace('translations.', '', $display);
        $this->displayWithRelations = $display;

        return $this;
    }

    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;

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

    public function isDisabled()
    {
        return $this->isDisabled;
    }

    public function setReadonly($value)
    {
        $this->isDisabled = $value;

        return $this;
    }

    public function makeNullable()
    {
        return $this->makeNullable;
    }

    public function nullable()
    {
        $this->makeNullable = true;

        $this->addValidationRule('nullable');

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

    public function getTemplateResult()
    {
        return $this->templateResult;
    }

    public function setTemplateResult($value)
    {
        $this->templateResult = $value;

        return $this;
    }

    public function getTemplateSelection()
    {
        return $this->templateSelection;
    }

    public function setTemplateSelection($value)
    {
        $this->templateSelection = $value;

        return $this;
    }

    public function prepareValue($value)
    {
        if ($this->makeNullable() && in_array($value, [''])) {
            return;
        }

        return parent::prepareValue($value);
    }

    public function getSearchUrl()
    {
        return $this->search_url ? $this->search_url :
            (route('admin.getSelectAjaxOptions') ??
             route('admin.form.element.' . static::$route, [
                 'adminModel' => \AdminSection::getModel($this->model)->getAlias(),
                 'field'      => $this->getName(),
                 'id'         => $this->model->getKey(),
             ]));
    }

    public function toArray()
    {
        $this->setHtmlAttributes([
            'id'                => $this->getName(),
            'size'              => 2,
            'data-select-type'  => 'single',
            'class'             => 'form-control js-data-smartSelect',
            'model'             => ! $this->isStaticOptions() ? get_class($this->getModelForOptions()) : null,
            'field'             => $this->getDisplay(),
            'placeholderText'   => $this->getPlaceholder() ?? 'Nothing selected',
            'relations'         => $this->displayWithRelations,
            'search'            => $this->getSearch(),
            'search_url'        => $this->getSearchUrl(),
            'data-min-symbols'  => $this->getMinSymbols(),
            'isReadOnly'        => $this->isDisabled(),
            'isNullable'        => $this->makeNullable(),
            'isStaticOptions'   => $this->isStaticOptions(),
            'queryFilters'      => json_encode($this->getQueryFilters()),
            'with'              => json_encode($this->getWith()),
            'templateResult'    => $this->getTemplateResult(),
            'templateSelection' => $this->getTemplateSelection(),
            'orderBy'           => $this->getOrderBy(),
            'limit'             => $this->getLimit(),
            'joins'             => $this->getJoins(),
            'selectRaws'        => $this->getSelectRaws(),
            'distinct'          => $this->getDistinct(),
            'cte'               => $this->getCte(),
        ]);

        if ($this->isDisabled()) {
            $this->setHtmlAttribute('readonly', '');
        }

        return ['attributes' => $this->getHtmlAttributes()] + Select::toArray();
    }
}
