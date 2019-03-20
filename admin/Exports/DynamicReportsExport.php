<?php

namespace Admin\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;

class DynamicReportsExport implements FromCollection, WithTitle

{
    protected $title;
    protected $data;

    public function __construct($data = null, $title)
    {
        $this->title = $title;
        $this->data  = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function title(): string
    {
        return $this->title;
    }
}