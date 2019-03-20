<?php

namespace Admin\Display\Column\Filter;

use App\Custom\CustomCollection;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Collection;
use SleepingOwl\Admin\Contracts\Repositories\RepositoryInterface;
use SleepingOwl\Admin\Display\Column\Filter\Select;

class CustomAjaxSelect extends Select
{
    protected $view = 'admin::column.filter.customAjaxSelect';

    public function initialize()
    {
        parent::initialize();

        $this->setHtmlAttribute('class', 'b-ajax-select2');
        $this->setHtmlAttribute('data-model', str_plural(snake_case(class_basename($this->getModelForOptions()))));
        $this->setHtmlAttribute('data-field', $this->getDisplay());
    }

    protected function loadOptions()
    {
        $repository = app(RepositoryInterface::class);
        $repository->setModel($this->getModelForOptions());
        $key = ($this->usageKey) ? $this->usageKey : $repository->getModel()->getKeyName();

        $options = $repository->getQuery();

        if ($this->isEmptyRelation() && ! is_null($foreignKey = $this->getForeignKey())) {
            $relation = $this->getModelAttributeKey();
            $model    = $this->getModel();

            if ($model->{$relation}() instanceof HasOneOrMany) {
                $options->where($foreignKey, 0)->orWhereNull($foreignKey);
            }
        }

        if (count($this->getFetchColumns()) > 0) {
            $options->select(
                array_merge([$key], $this->getFetchColumns())
            );
        }

        // call the pre load options query preparer if has be set
        if ( ! is_null($preparer = $this->getLoadOptionsQueryPreparer())) {
            $options = $preparer($this, $options);
        }

//        $options = $options->get();
        $options = new CustomCollection();

        //some fix for setUsage
        $key = str_replace('->', '.', $key);

        if (is_callable($makeDisplay = $this->getDisplay())) {
            // make dynamic display text
            if ($options instanceof Collection) {
                $options = $options->all();
            }

            // iterate for all options and redefine it as
            // list of KEY and TEXT pair
            $options = array_map(function ($opt) use ($key, $makeDisplay) {
                // get the KEY and make the display text
                return [data_get($opt, $key), $makeDisplay($opt)];
            }, $options);

            // take options as array with KEY => VALUE pair
            $options = array_pluck($options, 1, 0);
        } elseif ($options instanceof Collection) {
            // take options as array with KEY => VALUE pair
            $options = array_pluck($options->all(), $this->getDisplay(), $key);
        } else {
            // take options as array with KEY => VALUE pair
            $options = array_pluck($options, $this->getDisplay(), $key);
        }

        return $options;
    }
}
