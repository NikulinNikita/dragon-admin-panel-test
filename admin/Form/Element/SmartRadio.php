<?php

namespace Admin\Form\Element;

use Admin\Traits\AdminSmartComponentsTrait;
use SleepingOwl\Admin\Form\Element\Radio;

class SmartRadio extends Radio
{
    use AdminSmartComponentsTrait;

    public function __construct($path, $label = null, $options = [])
    {
        $label = $this->getSmartLabel($path, $label);
        parent::__construct($path, $label, $options);
    }
}
