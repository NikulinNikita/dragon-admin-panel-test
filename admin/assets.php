<?php

/**
 * @var KodiCMS\Assets\Contracts\MetaInterface $meta
 * @var KodiCMS\Assets\Contracts\PackageManagerInterface $packages
 *
 * @see http://sleepingowladmin.ru/docs/assets
 */

//$meta
//    ->css('custom', asset('custom.css'))
//    ->js('custom', asset('custom.js'), 'admin-default');

Meta::addCss('adminMain', asset('admin/css/adminMain.css'), 'admin-default');

Meta::addJs('custom', asset('admin/js/customjs/jquery.form.min.js'), 'admin-default');
Meta::addJs('moment', asset('admin/libs/moment/moment.min.js'), 'admin-default');
Meta::addJs('lodash', asset('admin/libs/lodash/lodash.min.js'), 'admin-default');

Meta::addJs('customAjaxSelect', asset('admin/js/customAjaxSelect.js'), 'admin-default');
Meta::addJs('smartSelect', asset('admin/js/smartSelect.js'), 'admin-default');
Meta::addJs('smartDepSelect', asset('admin/js/smartDepSelect.js'), 'admin-default');
Meta::addJs('exportReport', asset('admin/js/exportReport.js'), 'admin-default');
Meta::addJs('adminMain', asset('admin/js/adminMain.js'), 'admin-default');

$packages->add('AjaxResponse')
         ->js('alertify.min.js', asset('admin/libs/alertifyjs/js/alertify.min.js'), ['admin-default'], true)
         ->js('custom_functions.js', asset('admin/js/custom_functions.js'), ['admin-default'], true)
         ->js('custom.js', asset('admin/js/custom.js'), ['admin-default'], true);

$packages->add('Chart')
         ->js('Chart.min.js', asset('admin/libs/Chart/Chart.min.js'), ['admin-default'], true)
         ->js('getChart.js', asset('admin/js/getChart.js'), ['admin-default'], true);

$packages->add('stopRefresh')
         ->js('tree', asset('admin/js/customjs/stopPageRefresh.js'), ['admin-default'], true);

Meta::addJsMix('js/admin-app.js', 'adminMain', true);