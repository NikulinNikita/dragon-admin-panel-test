<?php

namespace Admin\Facades;

use AdminDisplay;
use AdminForm;
use AdminFormElement;
use Illuminate\Support\Facades\Facade;

class AdminDisplayTabbedComponent extends Facade
{
    public static function getTranslations($fields, $validationParams = [])
    {
        $component = AdminDisplay::tabbed()->setTabs(function () use ($fields, $validationParams) {
            $tabs = [];
            foreach (config('translatable.locales') as $k => $locale) {
                $elements      = [];
                $fieldsCounter = 0;

                foreach ($fields as $fieldName => $fieldTitle) {
                    if (is_int($fieldName)) {
                        $fieldName  = $fieldTitle;
                        $fieldTitle = ucfirst(strpos(trans("admin/common.description"), ".{$fieldTitle}") ? $fieldTitle : trans("admin/common.{$fieldTitle}"));
                    }
                    if ($fieldName === 'description') {
                        $element = AdminFormElement::textarea("description:{$locale}",
                            strpos(trans("admin/common.description"), '.description') ? 'Description' : trans("admin/common.description"))->setRows(5);
                    } else {
                        $element = AdminFormElement::text("{$fieldName}:{$locale}", "{$fieldTitle}")
                                                   ->setValidationRules($k === 0 && $fieldsCounter === 0 ? 'required' : '');

                        if ($validationParams && array_key_exists($fieldName, $validationParams)) {
                            $rules = $element->getValidationRules()["{$fieldName}:{$locale}"];
                            $rules = $rules ? array_merge($rules, $validationParams[$fieldName]) : $validationParams[$fieldName];
                            $element->setValidationRules($rules);
                        }
                    }

                    array_push($elements, $element);
                    $fieldsCounter++;
                }

                $tabs[] = AdminDisplay::tab(
                    AdminForm::elements($elements)
                )->setLabel(strtoupper($locale));
            }

            return $tabs;
        });

        return $component;
    }
}
