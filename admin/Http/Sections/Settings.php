<?php

namespace Admin\Http\Sections;

use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminDisplayFilter;
use AdminForm;
use AdminFormElement;
use Illuminate\Support\Facades\Cache;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class Settings extends BaseSection
{
    public $canDelete = false;

    public function initialize()
    {
        $this->updated(function ($config, $model) {
            if ($model->key == 'users_ip_whitelist') {
                $ips       = $model->value ? explode(',', $model->value) : [];
                $expiresAt = now()->addMinutes(env('CACHE_EXPIRATION_TIME'));

                Cache::put('WHITELIST_IP', $ips, $expiresAt);
            }
        });
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $tabs = AdminDisplay::tabbed();
        foreach (config('selectOptions.settings.type') as $settingType) {
            $display = AdminDisplay::table()->setHtmlAttribute('class', 'table-default table-hover');
            $display->setApply(function ($query) use ($settingType, $model) {
                $query->where("{$model->getTable()}.type", $settingType);
            });
            $display->paginate(config('selectOptions.common.adminPagination'));

            $display->setFilters(
                AdminDisplayFilter::field('key')->setTitle('Key: [:value]'),
                AdminDisplayFilter::field('value')->setTitle('Value: [:value]')
            );

            $display->setColumns([
                AdminColumn::sLink('id', '#')->setWidth('30px'),
                AdminColumn::sText('key', $model),
                AdminColumn::sText('value', $model)->append(AdminColumn::filter('value'))->setShowTags(true),
                AdminColumnEditable::sSelect('type', $model)->setEnum(config('selectOptions.settings.type')),
            ]);

            $tabs->appendTab($display, ucwords(trans("admin/{$table}.{$settingType}")))->setIcon('<i class="fa fa-info"></i>');
        }

        return $tabs;
    }

    public function onEdit($id)
    {
        $model    = $this->getModel();
        $instance = $model->whereId($id)->first();

        $valueField = AdminFormElement::sText('value', $model);

        if ($instance) {
            switch ($instance->key) {
                case 'reports_operational_start_date':
                    $valueField = AdminFormElement::sDate('value', $model);
                    break;
                case 'ui_unblocking_time':
                    $valueField = AdminFormElement::sDateTime('value', $model)
                        ->setFormat('Y-m-d H:i:s');
            }
        }

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('key', $model)->setValidationRules(['max:45|required']),
                    $valueField,
                    AdminFormElement::sSelect('type', $model)->setEnum(config('selectOptions.settings.type'))->setValidationRules(['required']),
                ],
                [
                    //
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        return $form;
    }
}
