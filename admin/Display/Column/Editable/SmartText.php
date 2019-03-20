<?php

namespace Admin\Display\Column\Editable;

use Admin\Traits\AdminSmartComponentsTrait;
use SleepingOwl\Admin\Display\Column\Editable\Text;

class SmartText extends Text
{
    use AdminSmartComponentsTrait;

    public function __construct($name, $label = null)
    {
        $label = $this->getSmartLabel($name, $label);
        parent::__construct($name, $label);
    }
}
