<?php

namespace Admin\Form\Element;

use Admin\Traits\AdminSmartComponentsTrait;
use SleepingOwl\Admin\Form\Element\Image;

class SmartImage extends Image
{
    use AdminSmartComponentsTrait;

    public function __construct($path, $label = null)
    {
        $label = $this->getSmartLabel($path, $label);
        parent::__construct($path, $label);
    }
}
