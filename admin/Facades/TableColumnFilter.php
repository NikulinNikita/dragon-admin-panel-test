<?php

namespace Admin\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \SleepingOwl\Admin\Display\Column\Filter\Text text()
 * @method static \SleepingOwl\Admin\Display\Column\Filter\Date date()
 * @method static \SleepingOwl\Admin\Display\Column\Filter\Select select()
 * @method static \SleepingOwl\Admin\Display\Column\Filter\Range range()
 * @method static \SleepingOwl\Admin\Display\Column\Filter\DateRange daterange()
 *
 * @method static \Admin\Display\Column\Filter\CustomDate customDate()
 * @method static \Admin\Display\Column\Filter\CustomAjaxSelect customAjaxSelect()
 * @method static \Admin\Display\Column\Filter\SmartSelect sSelect($options = [], $title = null)
 */
class TableColumnFilter extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sleeping_owl.column_filter';
    }
}
