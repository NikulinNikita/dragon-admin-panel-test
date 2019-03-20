<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\TranslationMetaData;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;

class Tills extends BaseSection
{
    public $canCreate = false;
    public $canEdit = false;
    public $canDelete = false;

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_header_and_pagination');
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[0, 'asc']]);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('title', $model)->setMetaData(TranslationMetaData::class),
            AdminColumn::sText('description', $model)->setMetaData(TranslationMetaData::class),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
        ]);

        $display->getColumns()->disableControls();

        return $display;
    }
}
