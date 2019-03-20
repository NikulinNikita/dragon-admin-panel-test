<?php

namespace Admin\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultipleSheetsExport implements WithMultipleSheets

{
    use Exportable;

    protected $dataArr;

    public function __construct($dataArr = [])
    {
        $this->dataArr = $dataArr;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->dataArr as $k => $data) {
            $sheets[] = new DynamicReportsExport($data, is_integer($k) ? $k + 1 : $k);
        }

        return $sheets;
    }
}