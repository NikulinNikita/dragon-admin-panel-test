<?php

namespace Admin\Display\Column;

use Admin\Traits\AdminSmartComponentsTrait;
use SleepingOwl\Admin\Display\Column\Lists;

class SmartLists extends Lists
{
    use AdminSmartComponentsTrait;

    protected $view = 'admin::column.smartLists';
    protected $uniqueOnly = false;

    public function __construct($name, $label = null, $small = null)
    {
        $label = $this->getSmartLabel($name, $label);
        parent::__construct($name, $label, $small);
    }

    public function getUniqueOnly()
    {
        return $this->uniqueOnly;
    }

    public function setUniqueOnly($value)
    {
        $this->uniqueOnly = $value;

        return $this;
    }

    public function toArray()
    {
        return [
                   'values'   => $this->getUniqueOnly() ? $this->getModelValue()->unique() : $this->getModelValue(),
                   'append'   => $this->getAppends(),
                   'showTags' => $this->getShowTags(),
               ] + parent::toArray();
    }
}
