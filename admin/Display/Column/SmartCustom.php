<?php

namespace Admin\Display\Column;

use Admin\Traits\AdminSmartComponentsTrait;
use Closure;
use SleepingOwl\Admin\Display\Column\Custom;

class SmartCustom extends Custom
{
    use AdminSmartComponentsTrait;

    protected $view = 'admin::column.smartCustom';

    public function __construct($name = null, $label = null, Closure $callback = null)
    {
        $label = $this->getSmartLabel(str_replace(' ', '', $name), $label);
        parent::__construct($name, $callback);
        $this->setLabel($label);
    }

    public function toArray()
    {
        return parent::toArray() + [
                'showTags'  => $this->getShowTags(),
            ];
    }
}
