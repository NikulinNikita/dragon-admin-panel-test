<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\TranslationMetaData;
use Admin\Facades\AdminDisplayTabbedComponent;
use AdminColumn;
use AdminColumnEditable;
use AdminColumnFilter;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use App\Models\Currency;
use Illuminate\Support\Facades\Cache;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;

class Regions extends BaseSection
{
    public $canDelete = false;

    public function initialize()
    {
        $this->updated(function ($config, $model) {
            $blockedCountries = \DB::table('regions')
                                   ->where('blocked', 1)
                                   ->pluck('iso')
                                   ->toArray();

            $expiresAt = now()->addMinutes(env('CACHE_EXPIRATION_TIME'));

            Cache::put('BLOCKED_COUNTRIES_ISO', $blockedCountries, $expiresAt);
        });
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'asc']]);
        $display->with(['currency']);

        $display->setColumnFilters([
            null,
            AdminColumnFilter::sSelect(Currency::class, 'code')->setColumnName('currency_id')->multiple(),
            AdminColumnFilter::text(),
            AdminColumnFilter::text(),
            AdminColumnFilter::text(),
            AdminColumnFilter::sSelect()->setEnum(config('selectOptions.common.status'))->multiple(),
            AdminColumnFilter::sSelect([trans("admin/common.no"), trans("admin/common.yes")])->setColumnName('blocked')->multiple(),
        ])->setPlacement('table.header');

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('currency.code', $model)->setOrderable(true),
            AdminColumn::sText('iso', $model),
            AdminColumn::sText('title', $model)->setMetaData(TranslationMetaData::class),
            AdminColumn::sText('description', $model)->setMetaData(TranslationMetaData::class),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
            AdminColumnEditable::sCheckbox('blocked', 'yes', 'no', $model),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();

        $region = AdminForm::panel();
        $region->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sSelect('currency_id', $model, Currency::class)->setDisplay('code')->setValidationRules(['required']),
                    AdminFormElement::sText('iso', $model)->setValidationRules(['min:2|max:2|required']),
                    ! is_null($id) ? AdminFormElement::sText('slug', $model)->setReadonly(true) : AdminFormElement::hidden('slug'),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')
                                    ->setValidationRules(['required']),
                    AdminFormElement::sCheckbox('blocked', $model)
                ],
                [
                    AdminDisplayTabbedComponent::getTranslations(['title', 'description'], ['title' => ['max:191']]),
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => new SaveAndClose(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        return $region;
    }
}
