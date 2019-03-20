<?php

namespace Admin\Display\Column;

use Admin\Traits\AdminSmartComponentsTrait;
use SleepingOwl\Admin\Display\Column\Text;

class SmartText extends Text
{
    use AdminSmartComponentsTrait;

    protected $view = 'admin::column.smartText';

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
