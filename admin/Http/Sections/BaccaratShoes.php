<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\DateTimeMetaData;
use Admin\Facades\AdminColumnFilterComponent;
use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Models\BaccaratCard;
use App\Models\BaccaratRound;
use App\Models\Staff;
use App\Models\Table;

class BaccaratShoes extends BaseSection
{
    public $canCreate = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[5, 'desc']]);
        $display->with(['staff', 'excludingCard']);

        if ( ! array_get($scopes, 'noFilters')) {
            $display->setColumnFilters([
                null,
                AdminColumnFilter::sSelect(Table::class, 'id')->setColumnName('table_id')->multiple(),
                AdminColumnFilter::sSelect(Staff::class, 'name')->setColumnName('staff_Id')->multiple()
                                 ->setJoins(['belongsToMany->roles'])->setQueryFilters([['roles.name', 'dealer']]),
                AdminColumnFilter::sSelect(BaccaratCard::class, 'title')->setColumnName('excluding_card_id')->multiple(),
                AdminColumnFilter::sSelect()->setEnum(config('selectOptions.baccarat_shoes.status'))->multiple(),
                AdminColumnFilterComponent::rangeDate(),
                AdminColumnFilterComponent::rangeDate(),

            ])->setPlacement('table.header');
        }

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sRelatedLink('table_id', $model),
            AdminColumn::sRelatedLink('staff.name', $model),
            AdminColumn::sText('excludingCard.title', $model),
            AdminColumn::sText('status', $model),
            AdminColumn::sText('created_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
            AdminColumn::sText('closed_at', $model)->setWidth('150px')->setMetaData(DateTimeMetaData::class),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $form = AdminForm::panel();
        $form->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('id', $model)->setHtmlAttribute('disabled', 'disabled'),
                    AdminFormElement::sText('table_id', $model)->setHtmlAttribute('disabled', 'disabled'),
                    AdminFormElement::sSelect('staff_id', $model, Staff::class)->setDisplay('name')->setHtmlAttribute('disabled', 'disabled'),
                ],
                [
                    AdminFormElement::sText('status', $model)->setHtmlAttribute('disabled', 'disabled'),
                    AdminFormElement::sText('created_at', $model)->setHtmlAttribute('disabled', 'disabled'),
                    AdminFormElement::sText('closed_at', $model)->setHtmlAttribute('disabled', 'disabled'),
                ]
            ])
        )->getButtons()->setButtons([]);

        $roundsBaccarat = AdminSection::getModel(BaccaratRound::class)->fireDisplay(['scopes' => ['baccarat_shoe_id' => $id, 'noFilters' => true]]);

        $tabs = AdminDisplay::tabbed();

        $tabs->appendTab($form, trans("admin/{$table}.tabs.ShoeInfo"))->setIcon('<i class="fa fa-info"></i>');

        if ( ! is_null($id)) {
            $tabs->appendTab($roundsBaccarat, trans("admin/{$table}.tabs.RoundsBaccarat"))->setIcon('<i class="fa fa-credit-card"></i>');
        }

        return $tabs;
    }
}
