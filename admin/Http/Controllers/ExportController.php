<?php

namespace Admin\Http\Controllers;

use Admin\Exports\MultipleSheetsExport;
use Admin\Exports\StaticReportsExport;
use App\Models\BaseModel;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use SleepingOwl\Admin\Contracts\ModelConfigurationInterface;
use SleepingOwl\Admin\Display\Column\Control;

class ExportController extends Controller
{
    public function exportStaticReport($type, Request $request)
    {
        list($filename, $filepath) = $this->getFilenameAndFilepath($type);

        $viewWithData = app()->call("Admin\Http\Controllers\ReportsController@getReports", ['page' => snake_case($type), 'exportToExcel' => true]);

        return Excel::download(new StaticReportsExport(null, null, $viewWithData), $filename);
    }

    public function exportReport(ModelConfigurationInterface $model, Request $request)
    {
        ['resp' => $dataSet, 'params' => $params] = $request->all();

        $dataSet             = array_get(json_decode($dataSet, true), 'data');
        $params              = $params ? json_decode($params, true) : null;
        $separateToSheets    = array_get($params, 'separateToSheets') ? json_decode(array_get($params, 'separateToSheets'), true) : null;
        $headerArray         = $this->getHeadersArray($model);
        $totalsArray         = $this->getTotalsArray($model);
        $data                = [];
        $resultedTotalsArray = [];

        if ($dataSet) {
            foreach ($dataSet as $kSet => $set) {
                foreach ($set as $kField => $field) {
                    $value                = trim(strip_tags($field));
                    $data[$kSet][$kField] = $value;
                    if ($kField === 0 || array_get($headerArray, $kField) === '#') {
                        $resultedTotalsArray[$kField] = trans("admin/common.Total") . ':';
                    } else if ($kField && isset($totalsArray[$kField]) && $totalsArray[$kField] && $totalsArray[$kField] != '' &&
                               $totalsArray[$kField] != 'Total:' && !strpos($value, '-')) {
                        $resultedTotalsArray[$kField] = array_get($resultedTotalsArray, $kField) + (float)$value;
                        $resultedTotalsArray[$kField] = $resultedTotalsArray[$kField] != 0 ? $resultedTotalsArray[$kField] : '0';
                    } else  {
                        $resultedTotalsArray[$kField] = '0';
                    }
                }
            }
        }
        array_unshift($data, $headerArray);
        array_push($data, $resultedTotalsArray);

        if ($separateToSheets) {
            $startFrom   = 0;
            $chunkedData = [];
            foreach ($separateToSheets as $i => $separatedSheet) {
                [$sheetName, $sheetSeparator] = $separatedSheet;
                foreach ($data as $k => $line) {
                    $chunkedData[$k] = array_slice($line, $startFrom, $sheetSeparator);
                }
                $resultedData[$sheetName] = $chunkedData;
                $startFrom                = $startFrom + $sheetSeparator;
            }
            if(count($data) && count(array_first($data)) > $startFrom) {
                foreach ($data as $k => $line) {
                    $chunkedData[$k] = array_slice($line, $startFrom, 100);
                }
                $resultedData['Others'] = $chunkedData;
            }
        } else {
            $resultedData = [$data];
        }

        list($filename, $filepath) = $this->getFilenameAndFilepath($model->getAlias());
        $file     = Excel::store(new MultipleSheetsExport($resultedData), $filename);
        $response = array(
            'name' => $filename,
            'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," . base64_encode(file_get_contents($filepath))
        );

        return response()->json($response);
    }

    protected function getFilenameAndFilepath($model)
    {
        $filename = snake_case($model) . "-" . Carbon::now()->format(config('selectOptions.common.dateTime')) . "-" . substr(\Hash::make(mt_rand(1, 100)),
                -5, 5);
        $filename = str_replace([' ', ':'], '_', $filename) . ".xlsx";
        $filepath = storage_path("app/" . $filename);

        return [$filename, $filepath];
    }

    protected function getHeadersArray(ModelConfigurationInterface $model)
    {
        return $model->onDisplay()->getColumns()->all()->filter(function ($column) {
            return ! $column instanceof Control;
        })->map(function ($column) {
            $value = $column->getHeader()->getTitle();

            return ! is_object($value) ? $value : '';
        })->toArray();
    }

    protected function getTotalsArray(ModelConfigurationInterface $model)
    {
        $totalsArr    = [];
        $columnsTotal = $model->onDisplay()->getColumnsTotal();
        $columnsTotal = BaseModel::accessProtected($columnsTotal, 'elements');
        foreach ($columnsTotal as $k => $columnTotal) {
            $totalsArr[$k] = trim(strip_tags($columnTotal->getText()));
        }

        return $totalsArr;
    }
}