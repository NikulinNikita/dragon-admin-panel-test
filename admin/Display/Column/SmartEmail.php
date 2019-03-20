<?php

namespace Admin\Display\Column;

use Admin\Traits\AdminSmartComponentsTrait;
use SleepingOwl\Admin\Display\Column\Email;

class SmartEmail extends Email
{
    use AdminSmartComponentsTrait;

    protected $view = 'admin::column.smartEmail';

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
