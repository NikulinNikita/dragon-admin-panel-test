<?php

namespace Admin\Form\Element;

use Admin\Traits\AdminSmartComponentsTrait;
use Illuminate\Database\Eloquent\Collection;
use SleepingOwl\Admin\Form\Element\MultiSelect;

class SmartMultiSelect extends MultiSelect
{
    use AdminSmartComponentsTrait;

    public function __construct($path, $label = null, $options = [])
    {
        $label = $this->getSmartLabel($path, $label);
        parent::__construct($path, $label, $options);
    }

    public function getValueFromModel()
    {
        $value = parent::getValueFromModel();

        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = (int)$val;
            }
        }

        if ($value instanceof Collection && $value->count() > 0) {
            $value = $value->pluck($value->first()->getKeyName())->all();
        }

        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        return $value;
    }
}
