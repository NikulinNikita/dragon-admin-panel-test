<?php

namespace Admin\Http\Controllers;

class AdminController extends Controller
{
    private $parentBreadcrumb = 'home';

    public function getDashboard()
    {
        return $this->renderContent(
            $this->admin->template()->view('dashboard'),
            trans('sleeping_owl::lang.dashboard')
        );
    }
}