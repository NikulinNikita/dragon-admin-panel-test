<?php

namespace Admin\Display\Column\Editable;

use Admin\Traits\AdminSmartComponentsTrait;
use SleepingOwl\Admin\Display\Column\Editable\Select;

class SmartSelect extends Select
{
    use AdminSmartComponentsTrait;

    public function __construct($name, $label = null, $options = [])
    {
        $label = $this->getSmartLabel($name, $label);
        parent::__construct($name, $label, $options);
    }
}
