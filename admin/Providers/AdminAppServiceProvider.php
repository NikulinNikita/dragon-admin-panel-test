<?php

namespace Admin\Providers;

use Admin\Display\Column\{
    SmartCustom as ColumnSmartCustom, SmartEmail as ColumnSmartEmail, SmartLink as ColumnSmartLink, SmartLists as ColumnSmartLists
};
use Admin\Display\Column\{
    SmartRelatedLink as ColumnSmartRelatedLink, SmartText as ColumnSmartText, SmartDateTime as ColumnSmartDateTime
};
use Admin\Display\Column\Editable\{
    SmartCheckbox as ColumnEditableSmartCheckbox, SmartSelect as ColumnEditableSmartSelect, SmartText as ColumnEditableSmartText
};
use Admin\Display\Column\Filter\{
    CustomAjaxSelect, SmartSelect as ColumnFilterSmartSelect, CustomDate
};
use Admin\Form\Element\{
    SmartDepSelect as ElementSmartDepSelect, SmartImage as ElementSmartImage, SmartDateTime as ElementSmartDateTime, SmartPassword as ElementSmartPassword
};
use Admin\Form\Element\{
    SmartMultiSelect as ElementSmartMultiSelect, SmartSelect as ElementSmartSelect, SmartText as ElementSmartText, SmartTextArea as ElementSmartTextArea
};
use Admin\Form\Element\{
    SmartCKEditor as ElementSmartCKEditor, SmartDate as ElementSmartDate, SmartCheckbox as ElementSmartCheckbox, SmartNumber as ElementSmartNumber
};
use Admin\Form\Element\{
    SmartRadio as ElementSmartRadio
};
use Illuminate\Support\ServiceProvider;

class AdminAppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $columnFilterContainer = app('sleeping_owl.column_filter');
        $columnFilterContainer->add('customDate', CustomDate::class);
        $columnFilterContainer->add('customAjaxSelect', CustomAjaxSelect::class);
        $columnFilterContainer->add('sSelect', ColumnFilterSmartSelect::class);

        $columnContainer = app('sleeping_owl.table.column');
        $columnContainer->add('sCustom', ColumnSmartCustom::class);
        $columnContainer->add('sDateTime', ColumnSmartDateTime::class);
        $columnContainer->add('sLists', ColumnSmartLists::class);
        $columnContainer->add('sText', ColumnSmartText::class);
        $columnContainer->add('sLink', ColumnSmartLink::class);
        $columnContainer->add('sRelatedLink', ColumnSmartRelatedLink::class);
        $columnContainer->add('sEmail', ColumnSmartEmail::class);

        $columnEditableContainer = app('sleeping_owl.table.column.editable');
        $columnEditableContainer->add('sCheckbox', ColumnEditableSmartCheckbox::class);
        $columnEditableContainer->add('sText', ColumnEditableSmartText::class);
        $columnEditableContainer->add('sSelect', ColumnEditableSmartSelect::class);

        $formElementContainer = app('sleeping_owl.form.element');
        $formElementContainer->add('sText', ElementSmartText::class);
        $formElementContainer->add('sImage', ElementSmartImage::class);
        $formElementContainer->add('sDate', ElementSmartDate::class);
        $formElementContainer->add('sDateTime', ElementSmartDateTime::class);
        $formElementContainer->add('sPassword', ElementSmartPassword::class);
        $formElementContainer->add('sMultiSelect', ElementSmartMultiSelect::class);
        $formElementContainer->add('sSelect', ElementSmartSelect::class);
        $formElementContainer->add('sDepSelect', ElementSmartDepSelect::class);
        $formElementContainer->add('sCheckbox', ElementSmartCheckbox::class);
        $formElementContainer->add('sCKEditor', ElementSmartCKEditor::class);
        $formElementContainer->add('sTextArea', ElementSmartTextArea::class);
        $formElementContainer->add('sRadio', ElementSmartRadio::class);
        $formElementContainer->add('sNumber', ElementSmartNumber::class);
    }
}