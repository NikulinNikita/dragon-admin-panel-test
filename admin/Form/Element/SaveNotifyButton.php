<?php

namespace Admin\Form\Element;

use SleepingOwl\Admin\Form\Buttons\FormButton;

class SaveNotifyButton extends FormButton
{
    protected $show = true;
    protected $name = 'save_and_notify';
    protected $iconClass = 'fa-check';
    protected $next_action = 'save_notify';

    public function __construct()
    {
        $this->setText(trans("admin/common.SaveAndNotify"));
        $this->setHtmlAttributes($this->getHtmlAttributes() + [
            'type'  => 'submit',
            'name'  => 'next_action',
            'class' => 'btn btn-info',
        ]);
    }
}