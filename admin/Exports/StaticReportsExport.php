<?php

namespace Admin\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class StaticReportsExport implements FromView
{
    protected $view;
    protected $data;
    protected $viewWithData;

    public function __construct($view, $data = null, $viewWithData = null)
    {
        $this->view         = $view;
        $this->data         = $data;
        $this->viewWithData = $viewWithData;
    }

    public function view(): View
    {
        return $this->viewWithData;
    }
}