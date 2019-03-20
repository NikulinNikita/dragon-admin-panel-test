<?php

namespace Admin\Display\Column\Editable;

use Admin\Traits\AdminSmartComponentsTrait;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Display\Column\Editable\Checkbox;

class SmartCheckbox extends Checkbox
{
    use AdminSmartComponentsTrait;

    public function __construct($name, $checkedLabel = null, $uncheckedLabel = null, $columnLabel = null)
    {
        if ($checkedLabel instanceof Model) {
            $checkedLabel = $this->getSmartLabel($name, $checkedLabel);
        } else {
            $checkedLabel   = $checkedLabel ? $this->getSmartLabel($checkedLabel, $columnLabel) : null;
            $uncheckedLabel = $uncheckedLabel ? $this->getSmartLabel($uncheckedLabel, $columnLabel) : null;
            $columnLabel    = $this->getSmartLabel($name, $columnLabel);
        }
        parent::__construct($name, $checkedLabel, $uncheckedLabel, $columnLabel);
    }
}
