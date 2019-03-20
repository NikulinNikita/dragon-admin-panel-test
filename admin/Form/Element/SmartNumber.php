<?php

namespace Admin\Form\Element;

use Admin\Traits\AdminSmartComponentsTrait;
use SleepingOwl\Admin\Form\Element\Number;

class SmartNumber extends Number
{
    use AdminSmartComponentsTrait;

    public function __construct($path, $label = null)
    {
        $label = $this->getSmartLabel($path, $label);
        parent::__construct($path, $label);
    }
}
