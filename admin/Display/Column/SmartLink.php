<?php

namespace Admin\Display\Column;

use Admin\Traits\AdminSmartComponentsTrait;
use SleepingOwl\Admin\Display\Column\Link;

class SmartLink extends Link
{
    use AdminSmartComponentsTrait;

    protected $view = 'admin::column.smartLink';

    public function __construct($name, $label = null, $small = null)
    {
        $label = $this->getSmartLabel($name, $label);
        parent::__construct($name, $label, $small);
    }

    public function toArray()
    {
        return parent::toArray() + [
                'showTags'  => $this->getShowTags(),
            ];
    }
}
