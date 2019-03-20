<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use Admin\ColumnMetas\TranslationMetaData;
use Admin\Facades\AdminDisplayTabbedComponent;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Models\Currency;
use App\Models\UserStatusLimit;
use DB;
use Illuminate\Database\Eloquent\Model;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\Save;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\FormElements;

class UserStatuses extends BaseSection
{
    public $canDelete = false;

    public function initialize()
    {
        $this->created(function ($config, Model $model) {
            DB::beginTransaction();
            try {
                foreach (Currency::all() as $currency) {
                    $nominalValue = UserStatusLimit::create([
                        'currency_id'    => $currency->id,
                        'user_status_id' => $model->id,
                        'limit'          => 0,
                    ]);
                }
            } catch (\Exception $e) {
                DB::rollback();
                $model->delete();
                throw $e;
            }
            DB::commit();
        });
    }

    public function onDisplay($scopes = [])
    {
        $model = $this->getModel();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover b-remove_pagination');
        $display->paginate(9999)->setOrder([[0, 'asc']]);

        $display->setColumns([
            AdminColumn::sLink('id', '#')->setWidth('30px'),
            AdminColumn::sText('title', $model)->setMetaData(TranslationMetaData::class),
            AdminColumnEditable::sText('description', $model)->setMetaData(TranslationMetaData::class),
            AdminColumnEditable::sText('multiplier', $model),
            AdminColumnEditable::sText('duration', $model),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
        ]);

        return $display;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();

        $userStatuses = AdminForm::panel();
        $userStatuses->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('multiplier', $model),
                    AdminFormElement::sText('duration', $model),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setDefaultValue('active')
                                    ->setValidationRules(['required']),
                ],
                [
                    AdminDisplayTabbedComponent::getTranslations(['title', 'description'], ['title' => ['max:191']]),
                ]
            ]),
            new  FormElements(['<hr>']),
            ! is_null($id) ? AdminSection::getModel(UserStatusLimit::class)->fireDisplay(['scopes' => ['user_status_id' => $id]]) : ''
        )->getButtons()->setButtons([
            'save'   => ! is_null($id) ? new SaveAndClose() : new Save(),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);


        return $userStatuses;
    }
}
