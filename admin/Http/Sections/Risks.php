<?php

namespace Admin\Http\Sections;

use AdminColumn;
use AdminColumnFilter;
use AdminDisplay;
use App\Models\RiskType;

class Risks extends BaseSection
{
    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'desc']]);
        $display->with(['riskType']);

        if ( ! array_get($scopes, 'noFilters')) {
            $display->setColumnFilters(array(
                null,
                null,
                AdminColumnFilter::sSelect(RiskType::class, 'name')->setColumnName('risk_type_id')->multiple(),
            ))->setPlacement('table.header');
        }

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('code', $model),
            AdminColumn::sRelatedLink('riskType.name', $model)->setOrderable(true),
            AdminColumn::sText('description', $model),
        ]);

        return $display;
    }
}
