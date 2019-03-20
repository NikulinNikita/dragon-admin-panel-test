<?php

namespace Admin\Facades;

use AdminColumnFilter;
use Illuminate\Support\Facades\Facade;

class AdminColumnFilterComponent extends Facade
{
    public static function rangeInput($dateType = 'date')
    {
        $component = AdminColumnFilter::range()
                                      ->setFrom(
                                          AdminColumnFilter::text()->setPlaceholder('From')
                                      )
                                      ->setTo(
                                          AdminColumnFilter::text()->setPlaceholder('To')
                                      );

        return $component;
    }

    public static function rangeDate($dateType = 'date')
    {
        $component = AdminColumnFilter::range()
                                      ->setFrom(
                                          AdminColumnFilter::customDate()->setPlaceholder('From Date')
                                                           ->setFormat(config("selectOptions.common.{$dateType}"))
                                                           ->setPickerFormat(config("selectOptions.common.{$dateType}"))
                                      )
                                      ->setTo(
                                          AdminColumnFilter::date()->setPlaceholder('To Date')
                                                           ->setFormat(config("selectOptions.common.{$dateType}"))
                                                           ->setPickerFormat(config("selectOptions.common.{$dateType}"))
                                      );

        return $component;
    }
}
