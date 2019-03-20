<?php

namespace Admin\Navigation;

use SleepingOwl\Admin\Navigation\Page as SleepingOwlPage;

class Page extends SleepingOwlPage
{
    protected $sBadge;

    public function __construct($modelClass = null)
    {
        parent::__construct($modelClass);

        $menuPriorityCounter = session("admin.menu.counter") + 1;
        session()->put("admin.menu.counter", $menuPriorityCounter);
        $this->setPriority($menuPriorityCounter);

        if ( ! $this->hasModel()) {
            $this->setTitle($menuPriorityCounter);
        }
    }

    public function add($page = null)
    {
        if ($page === null || is_string($page)) {
            $page = new Page($page);
        }

        if ($page = parent::addPage($page)) {
            $page->setParent($this);
        }

        return $page;
    }

    public function getSBadge()
    {
        return $this->sBadge;
    }

    public function sBadge($value)
    {
        $this->sBadge = $value;

        return $this;
    }

    public function render($view = null)
    {
        if ($this->getSBadge()) {
            $value = session("admin.menu.badges.{$this->getSBadge()}");
            $this->addBadge($value);
        }
        if ( ! $this->hasModel()) {
            $this->setTitle(trans("admin/navigation.{$this->getTitle()}"));
        }


        if ($this->hasChild() && ! $this->hasClassProperty($class = config('navigation.class.has_child', 'treeview'))) {
            $this->setHtmlAttribute('class', $class);
        }

        $data = $this->toArray();

        if ( ! is_null($view)) {
            return view($view, $data)->render();
        }

        return app('sleeping_owl.template')->view('_partials.navigation.page', $data)->render();
    }
}
