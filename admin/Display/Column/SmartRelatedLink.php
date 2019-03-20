<?php

namespace Admin\Display\Column;

use Admin\Traits\AdminSmartComponentsTrait;
use SleepingOwl\Admin\Display\Column\RelatedLink;

class SmartRelatedLink extends RelatedLink
{
    use AdminSmartComponentsTrait;

    protected $link;
    protected $isLink;
    protected $view = 'admin::column.smartLink';

    public function __construct($name, $label = null, $small = null)
    {
        $label = $this->getSmartLabel($name, $label);
        parent::__construct($name, $label, $small);
    }

    public function getLink()
    {
        return $this->link;
    }

    public function setLink($value)
    {
        $this->link = $value;
        $this->setIsLink(true);

        return $this;
    }

    public function isLink()
    {
        return $this->isLink;
    }

    public function setIsLink($value)
    {
        $this->isLink = $value;

        return $this;
    }

    public function toArray()
    {
        return [
                   'link'       => $this->getLink() ?? $this->getModelConfiguration()->getEditUrl($this->getModel()->getKey()),
                   'isEditable' => $this->isLink() ?? $this->isEditable(),
                   'showTags'   => $this->getShowTags(),
               ] + parent::toArray();
    }
}
