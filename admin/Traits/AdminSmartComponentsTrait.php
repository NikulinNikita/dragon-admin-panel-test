<?php

namespace Admin\Traits;

use Illuminate\Database\Eloquent\Model;

trait AdminSmartComponentsTrait
{
    protected $showTags = false;

    public function getSmartLabel($attr, $label = null)
    {
        if(is_array($label)) {
            [$label, $type] = $label;
        }

        $table = $label instanceof Model ? $label->getTable() : null;
        $modifiedAttr = str_replace('.', '->', $attr) . (isset($type) ? "->{$type}" : "");
        $label = $table ? trans("admin/{$table}.{$modifiedAttr}") :
            ($label ?: preg_replace("/(?<=\\w)(?=[A-Z])/", " $1", ucwords(camel_case($attr))));

        return $label;
    }

    public function getShowTags()
    {
        return $this->showTags;
    }

    public function setShowTags($value)
    {
        $this->showTags = $value;

        return $this;
    }
}