<?php

namespace Admin\Http\Sections;

use Admin\ColumnMetas\BaseMetaData;
use AdminColumn;
use AdminColumnEditable;
use AdminDisplay;
use AdminDisplayFilter;
use AdminForm;
use AdminFormElement;
use AdminSection;
use App\Models\BaccaratBet;
use App\Models\BaseModel;
use App\Models\BetsBankAccrual;
use App\Models\Currency;
use App\Models\Game;
use App\Models\Gateway;
use App\Models\Note;
use App\Models\Operation;
use App\Models\Region;
use App\Models\RiskEvent;
use App\Models\RouletteBet;
use App\Models\User;
use App\Models\UserAuthorization;
use App\Models\UserBonus;
use App\Models\UserBonusLimit;
use App\Models\UserStatusChange;
use App\Models\UserStatusPoint;
use Carbon\Carbon;
use DB;
use DragonStudio\BonusProgram\Events\UserBirthDayVerificationStatusChanged;
use DragonStudio\BonusProgram\Events\UserRegistered;
use DragonStudio\BonusProgram\Events\UserVerificationStatusChanged;
use SleepingOwl\Admin\Form\Buttons\Cancel;
use SleepingOwl\Admin\Form\Buttons\Delete;
use SleepingOwl\Admin\Form\Buttons\Save;
use SleepingOwl\Admin\Form\Buttons\SaveAndClose;
use SleepingOwl\Admin\Form\FormElements;

class Users extends BaseSection
{
    public $canDelete = false;

    public function initialize()
    {
        $this->created(function ($config, User $model) {
            DB::beginTransaction();
            try {
                event(new UserRegistered($model));
            } catch (\Exception $e) {
                DB::rollback();
                $model->delete();
                throw $e;
            }
            DB::commit();
        });

        $this->updated(function ($config, User $model) {
            DB::beginTransaction();
            try {
                event(new UserVerificationStatusChanged($model));
                event(new UserBirthDayVerificationStatusChanged($model));

                if (request()->get('bets_limit')) {
                    $options = $model->options;

                    $bets_limit = request()->get('bets_limit');

                    foreach ($bets_limit as $key => $limit) {
                        if (strlen($limit['bet_limit_min']) || strlen($limit['bet_limit_max'])) {
                            if (strlen($limit['bet_limit_min'])) {
                                $bets_limit[$key]['bet_limit_min'] = (int)$limit['bet_limit_min'];
                            } else {
                                unset($bets_limit[$key]['bet_limit_min']);
                            }

                            if (strlen($limit['bet_limit_max'])) {
                                $bets_limit[$key]['bet_limit_max'] = (int)$limit['bet_limit_max'];
                            } else {
                                unset($bets_limit[$key]['bet_limit_max']);
                            }
                        } else {
                            unset($bets_limit[$key]);
                        }
                    }

                    if ($options) {
                        $options->bets_limit = $bets_limit;
                    } else {
                        $options = ['bets_limit' => $bets_limit];
                    }

                    $model->options = $options;

                    $model->save();
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
        $table = $model->getTable();

        $display = AdminDisplay::datatablesAsync()->setHtmlAttribute('class', 'table-default table-hover');
        $display->setApply(function ($query) use ($scopes, $model) {
            if (array_get($scopes, 'type') === 'userId' || request()->get('userId')) {
                $query->where("{$model->getTable()}.id", array_get($scopes, 'userId') ?? request()->get('userId'));
            }
        });
        $display->paginate(config('selectOptions.common.adminPagination'))->setOrder([[4, 'desc']]);
        $display->with(['region.translations', 'currency', 'notes', 'userTills', 'baccaratBets']);

        if (array_get($scopes, 'displayType') === 'bets_bonus_amounts') {
            $display         = BetsBonusAmounts::setColumns($display, $model);
            $this->canCreate = false;

            return $display;
        }

        $display->setFilters(
            AdminDisplayFilter::custom('date_from')->setCallback(function ($query, $value) {
                $query->where('created_at', '>=', Carbon::parse($value));
            })->setTitle('Date From: [:value]'),
            AdminDisplayFilter::custom('date_to')->setCallback(function ($query, $value) {
                $query->where('created_at', '<=', Carbon::parse($value)->addDay()->subSecond());
            })->setTitle('Date To: [:value]'),
            AdminDisplayFilter::custom('bets_date_from')->setCallback(function ($query, $value) {
                return $query;
            })->setTitle('Bets DateTime From: [:value]'),
            AdminDisplayFilter::custom('bets_date_to')->setCallback(function ($query, $value) {
                return $query;
            })->setTitle('Bets DateTime To: [:value]'),
            AdminDisplayFilter::field('name')->setTitle('Name: [:value]')->setOperator('contains'),
            AdminDisplayFilter::field('id')->setTitle('ID: [:value]'),
            request()->get('ids') ?
                AdminDisplayFilter::field('id')->setAlias('ids')->setOperator('in')->setTitle(function ($value) {
                    $result = implode(', ', $value);

                    return "User IDs: [{$result}]";
                }) : AdminDisplayFilter::field('xxx'),
            AdminDisplayFilter::custom('region')->setCallback(function ($query, $value) {
                $query->whereHas('region.translations', function ($q) use ($value) {
                    return $q->where('title', $value);
                });
            })->setTitle('Region: [:value]'),
            AdminDisplayFilter::field('first_name')->setTitle('First Name: [:value]')->setOperator('contains'),
            AdminDisplayFilter::field('last_name')->setTitle('Last Name: [:value]')->setOperator('contains'),
            AdminDisplayFilter::field('middle_name')->setTitle('Middle Name: [:value]')->setOperator('contains'),
            AdminDisplayFilter::field('phone')->setTitle('Phone: [:value]')->setOperator('contains'),
            AdminDisplayFilter::field('mobile')->setTitle('Mobile: [:value]')->setOperator('contains'),
            AdminDisplayFilter::custom('birthday')->setCallback(function ($query, $value) {
                $query->whereDate('birthday', '=', $value);
            })->setTitle('Birthday: [:value]'),
            AdminDisplayFilter::field('birth_city')->setTitle('Birth City: [:value]')->setOperator('contains'),
            AdminDisplayFilter::field('gender')->setTitle('Gender: [:value]'),
            AdminDisplayFilter::field('status')->setTitle('Status: [:value]'),
            AdminDisplayFilter::field('document_verification')->setTitle('Verification: [:value]'),
            AdminDisplayFilter::field('birth_date_verification')->setTitle('Verification: [:value]'),
            AdminDisplayFilter::custom('balance_from')->setCallback(function ($query, $value) {
                $query->whereHas('userTills', function ($q) use ($value) {
                    return $q->where('till_id', 1)->where('balance', '>=', $value);
                });
            })->setTitle('Balance from: [:value]'),
            AdminDisplayFilter::custom('balance_to')->setCallback(function ($query, $value) {
                $query->whereHas('userTills', function ($q) use ($value) {
                    return $q->where('till_id', 1)->where('balance', '<', $value);
                });
            })->setTitle('Balance to: [:value]')
        );

        $display->setColumns([
            AdminColumn::sCustom('id', '#', function ($model) {
                $url    = $this->getActionUrl($model, 'edit');
                $labels = [
                    'no'                    => 'success',
                    'withdraw'              => 'warning',
                    'gameplay'              => 'info',
                    'withdraw_and_gameplay' => 'pink',
                    'login'                 => 'danger',
                    'personal_manager_only' => 'default'
                ];
                $label  = $labels[$model->blocked];

                return "<a href='{$url}' class='label label-{$label}'>{$model->id}</a>";
            })->setWidth('30px')->setShowTags(true)->setOrderable(true)->setMetaData(BaseMetaData::class),
            AdminColumn::sLink('name', $model),
            AdminColumn::sText('first_name', $model),
            AdminColumn::sText('last_name', $model),
            AdminColumn::sText('created_at', $model),
            AdminColumn::sCustom('Balance', $model, function (BaseModel $model) {
                $result = '';
                foreach ($model->userTills as $userTill) {
                    $balance = BaseModel::formatCurrency($model->currency_id, $userTill->balance);
                    $label   = [1 => 'warning', 2 => 'info', 3 => 'pink'];
                    $result  = $result . "<li><span class=\"label label-{$label[$userTill->till_id]}\">{$balance}</span></li>";
                }

                return $result;
            })->setHtmlAttribute('class', 'custom-list-items')->setWidth('100px')->setShowTags(true),
            AdminColumn::sText('AllBetsAmountFormatted', $model)->setOrderable(false),
            AdminColumn::sText('OutcomeAmountFormatted', $model)->setOrderable(false),
            AdminColumn::sText('ResultFormatted', $model)->setOrderable(false),
            AdminColumn::sCustom('CasinoHold', trans('admin/users.CasinoHold'), function (BaseModel $model) {
                return $model->CasinoHold . '%';
            })->setOrderable(false),
            AdminColumnEditable::sSelect('blocked', $model)->setEnum(config('selectOptions.users.blocked')),
            AdminColumnEditable::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))->setMetaData(BaseMetaData::class),
            AdminColumn::sCustom('Login', ' ', function (BaseModel $model) use ($table) {
                $html = '';

                if (auth()->user()->hasAnyRole(['superadmin', 'general', 'accountant'])) {
                    $url   = route('admin.user.manual_login', ['id' => $model->id]);
                    $title = trans("admin/{$table}.Login");

                    $html = "<a class=\"btn btn-xs text-center btn-warning\" href='{$url}' target='_blank' title='{$title}'><i class='fa fa-user'></i></a>";
                }

                return $html;
            })->setShowTags(true),
        ]);

        $tabs   = AdminDisplay::tabbed();
        $search = AdminForm::panel()->setView(view('admin::search.users'));

        $tabs->appendTab($display, trans("admin/{$table}.tabs.UsersList"))->setIcon('<i class="fa fa-info"></i>');
        $tabs->appendTab($search, trans("admin/{$table}.tabs.Search"))->setIcon('<i class="fa fa-info"></i>');

        return $tabs;
    }

    public function onEdit($id)
    {
        $model = $this->getModel();
        $table = $model->getTable();

        $mainInformation = AdminForm::panel();
        $mainInformation->addBody(
            AdminFormElement::columns([
                [
                    AdminFormElement::sText('name', $model)->setValidationRules(['min:3|max:45|required|_unique']),
                    AdminFormElement::sPassword('AdminPassword', $model)->setHtmlAttribute('autocomplete', 'off')->setValidationRules(['min:3|max:60|required']),
                    AdminFormElement::sText('email', $model)->mutateValue(function ($value) {
                        return $value === '' ? null : $value;
                    })->setHtmlAttribute('autocomplete', 'off')->setValidationRules(['max:191|email|_unique']),
                    AdminFormElement::sSelect('currency_id', $model, Currency::class)->setDefaultValue(1)->setDisplay('code')
                                    ->setValidationRules(['required|integer']),
                    AdminFormElement::sSelect('blocked', $model)->setEnum(config('selectOptions.users.blocked'))
                                    ->setDefaultValue('no')->setValidationRules(['required']),
                    AdminFormElement::sSelect('status', $model)->setEnum(config('selectOptions.common.status'))
                                    ->setDefaultValue('active')->setValidationRules(['required']),
                ],
                [
                    AdminFormElement::sImage('AdminPhoto', $model),
                    AdminFormElement::sText('nickname', $model)->setValidationRules(['min:3|max:45']),
                    AdminFormElement::sText('first_name', $model)->setValidationRules(['min:3|max:45']),
                    AdminFormElement::sText('last_name', $model)->setValidationRules(['min:3|max:45']),
                    AdminFormElement::sText('address', $model)->setValidationRules(['max:191']),
                    AdminFormElement::sText('phone', $model)->setValidationRules(['max:191']),
                    AdminFormElement::sText('mobile', $model)->setValidationRules(['max:191']),
                ]
            ])
        )->getButtons()->setButtons([
            'save'   => (new Save())->setGroupElements([
                'save_and_close' => new SaveAndClose(),
            ]),
            'delete' => new Delete(),
            'cancel' => new Cancel(),
        ]);

        $tabs = AdminDisplay::tabbed();
        $tabs->appendTab($mainInformation, trans("admin/{$table}.tabs.MainInfo"))->setIcon('<i class="fa fa-info"></i>');

        if ( ! is_null($id)) {
            $instance  = User::findOrFail($id);
            $moneyTill = $instance->userTills->where('till_id', 1)->first();

            $frontHost = env('FRONT_HOST');

            $bitrthdayDocFile     = isset($instance->options->birthday_verification_document)
                ? $instance->options->birthday_verification_document
                : null;
            $bitrthdayDocFileHtml = $bitrthdayDocFile
                ? "<a class=\"input-group\" href=\"${frontHost}/uploads/files/" . $bitrthdayDocFile . '" target="_blank">' . $bitrthdayDocFile . '</a>'
                : '<b class="input-group">' . trans("admin/common.no") . '</b>';
            $idDocFile            = isset($instance->options->identity_verification_document)
                ? $instance->options->identity_verification_document
                : null;
            $idDocFileHtml        = $idDocFile
                ? "<a class=\"input-group\" href=\"${frontHost}/uploads/files/" . $idDocFile . '" target="_blank">' . $idDocFile . '</a>'
                : '<b class="input-group">' . trans("admin/common.no") . '</b>';

            $birthday_verification_document =
                "<div class='form-group'>
                    <label for='birthday' class='control-label'>" . trans("admin/{$table}.options->birthday_verification_document") . "</label> 
                    {$bitrthdayDocFileHtml}
                </div>";
            $identity_verification_document =
                "<div class='form-group'>
                    <label for='birthday' class='control-label'>" . trans("admin/{$table}.options->identity_verification_document") . "</label> 
                    {$idDocFileHtml}
                </div>";

            $additionalInformation = AdminForm::panel();
            $additionalInformation->addBody(
                AdminFormElement::columns([
                    [
//                        AdminFormElement::sCheckbox('is_test', $model),
                        AdminFormElement::sSelect('gender', $model)->setEnum(config('selectOptions.common.gender')),
                        AdminFormElement::sSelect('language', $model)->setEnum(array_combine(\LaravelLocalization::getSupportedLanguagesKeys(),
                            \LaravelLocalization::getSupportedLanguagesKeys())),
                        AdminFormElement::sSelect('region_id', $model, Region::class)->setDisplay('translations.title')->setValidationRules(['required']),
                        AdminFormElement::sText('birth_city', $model),
                        AdminFormElement::sSelect('time_zone', $model)->setEnum(array_combine(\DateTimeZone::listIdentifiers(),
                            \DateTimeZone::listIdentifiers())),
                        AdminFormElement::sSelect('loyalty_user', $model)->setEnum(config('selectOptions.users.loyalty_user')),
//                        AdminFormElement::sText('loyalty_level', $model),
                    ],
                    [
//                        AdminFormElement::sImage('AdminDocumentPhoto', $model),
                        AdminFormElement::sDate('birthday', $model)->setPickerFormat(config('selectOptions.common.date'))
                                        ->setFormat(config('selectOptions.common.dateTimeDB')),
                        $birthday_verification_document,
                        $identity_verification_document,
                        AdminFormElement::sSelect('birth_date_verification', $model)->setEnum(config('selectOptions.users.verification')),
                        AdminFormElement::sSelect('document_verification', $model)->setEnum(config('selectOptions.users.verification')),
                        AdminFormElement::sText('document_number', $model),
                        AdminFormElement::sText('document_issue_code', $model),
                        AdminFormElement::sDate('AdminDocumentIssueDate', $model)->setPickerFormat(config('selectOptions.common.date'))
                                        ->setFormat(config('selectOptions.common.dateTime')),
                    ]
                ])
            )->getButtons()->setButtons([
                'save'   => (new Save())->setGroupElements([
                    'save_and_close' => new SaveAndClose(),
                ]),
                'delete' => new Delete(),
                'cancel' => new Cancel(),
            ]);

            $mainStats       = AdminForm::panel();
            $mainStats->type = 'user_main_stats';
            $mainStats->addBody([
                new  FormElements([
                    "<div class='' id='a-{$mainStats->type}'><div class='text-center'><i class='fa fa-5x fa-circle-o-notch fa-spin'></i></div></div>",
                ])
            ])->getButtons()->setButtons([]);

            $socialPrefs       = AdminForm::panel();
            $socialPrefs->type = 'social_prefs';
            $socialPrefs->addBody([
                new  FormElements([
                    "<div class='' id='a-{$socialPrefs->type}'><div class='text-center'><i class='fa fa-5x fa-circle-o-notch fa-spin'></i></div></div>",
                ])
            ])->getButtons()->setButtons([]);

            $notes = AdminSection::getModel(Note::class)->fireDisplay([
                'scopes' => [
                    'notable_id' => $id,
                    'type'       => 'user',
                    'noFilters'  => true,
                ]
            ]);

            $userGateways = AdminForm::panel();
            $userGateways->addBody(
                AdminFormElement::columns([
                    [
                        AdminFormElement::multiselect('gateways', trans("admin/{$table}.gateways"), Gateway::class)->setDisplay('title'),
                    ],
                    [
                        //
                    ]
                ])
            )->getButtons()->setButtons([
                'save'   => (new Save())->setGroupElements([
                    'save_and_close' => new SaveAndClose(),
                ]),
                'delete' => new Delete(),
                'cancel' => new Cancel(),
            ]);

            $operations = AdminSection::getModel(Operation::class)->fireDisplay([
                'scopes' => [
                    'user_till_id' => $moneyTill->id,
                ]
            ]);

            $authorizations = AdminSection::getModel(UserAuthorization::class)->fireDisplay([
                'scopes' => [
                    'user_id' => $id,
                ]
            ]);

            $gameHistory       = AdminForm::panel();
            $gameHistory->type = 'user_game_history';
            $gameHistory->addBody([
                AdminFormElement::columns([
                    [
                        new  FormElements([
                            "<div class='' id='a-{$gameHistory->type}'><div class='text-center'><i class='fa fa-5x fa-circle-o-notch fa-spin'></i></div></div><br>",
                        ])
                    ],
                ])->setHtmlAttribute('class', "b-mrb-10"),
                AdminFormElement::columns([[new FormElements(["<h3>Баккара</h3>"])]])->setHtmlAttribute('class', "b-mrb-10"),
                AdminFormElement::columns([
                    [
                        AdminSection::getModel(BaccaratBet::class)
                                    ->fireDisplay(['scopes' => ['user_till_id' => $moneyTill->id, 'noFilters' => true, 'noParentId' => true]])
                    ],
                ])->setHtmlAttribute('class', "b-mrb-10"),
                AdminFormElement::columns([[new FormElements(["<h3>Рулетка</h3>"])]])->setHtmlAttribute('class', "b-mrb-10"),
                AdminFormElement::columns([
                    [
                        AdminSection::getModel(RouletteBet::class)
                                    ->fireDisplay(['scopes' => ['user_till_id' => $moneyTill->id, 'noFilters' => true, 'noParentId' => true]])
                    ],
                ])->setHtmlAttribute('class', "b-mrb-10"),
            ])->getButtons()->setButtons([]);

            $userStatusPoint  = UserStatusPoint::where('user_id', $id)->first();
            $userStatusTitle  = $userStatusPoint->userStatus ? $userStatusPoint->userStatus->title : trans("admin/{$table}.status_points.no");
            $userStatusPoints = AdminForm::panel();
            $userStatusPoints->addBody([
                AdminFormElement::columns([
                    [
                        new  FormElements([
                            "<div class=''>
                                <h4><B>" . trans("admin/{$table}.status_points.title") . "</B></h4>
                                <p>" . trans("admin/{$table}.status_points.UserStatus") . ": <span class='badge'>{$userStatusTitle}</span></p>
                                <p>" . trans("admin/{$table}.status_points.UserStatusPoints") . ": <span class='badge'>{$userStatusPoint->points}</span></p>
                                <p>" . trans("admin/{$table}.status_points.ActivationDate") . ": <span class='badge'>{$userStatusPoint->activation_date}</span></p>
                            </div>",
                        ])
                    ],
                    [
                        AdminSection::getModel(UserStatusChange::class)->fireDisplay(['scopes' => ['user_id' => $id]])
                    ]
                ]),
            ])->getButtons()->setButtons([]);

            $bonuses = AdminSection::getModel(UserBonus::class)->fireDisplay([
                'scopes' => [
                    'user_id' => $id,
                ]
            ]);

            $personalBonusLimits = AdminSection::getModel(UserBonusLimit::class)->fireDisplay([
                'scopes' => [
                    'user_id' => $id
                ]
            ]);

            $games            = Game::all();
            $currentBetsLimit = isset($instance->options->bets_limit) ? $instance->options->bets_limit : [];

            $betsLimit       = AdminForm::panel();
            $betsLimit->type = 'user_bets_limit';
            $betsLimitHtml   = view('admin::user.bets_limits', ['games' => $games, 'currentBetsLimit' => $currentBetsLimit])->render();
            $betsLimit->addBody([
                AdminFormElement::columns([
                    [
                        new FormElements(
                            [
                                $betsLimitHtml
                            ]
                        )
                    ]
                ])
            ]);
            $betsLimit->getButtons()->setButtons([
                'save'   => new Save(),
                'cancel' => new Cancel(),
            ]);

            $riskEvents = AdminSection::getModel(RiskEvent::class)->fireDisplay(['scopes' => ['user_id' => $id,]]);

            $betsBankAccruals       = AdminForm::panel();
            $betsBankAccruals->type = 'bets_bank_accruals';
            $betsBankAccruals->addBody([
                AdminFormElement::columns([
                    [
                        new  FormElements([
                            "<div class='' id='a-{$betsBankAccruals->type}'><div class='text-center'><i class='fa fa-5x fa-circle-o-notch fa-spin'></i></div></div><br>",
                        ])
                    ],
                ])->setHtmlAttribute('class', "b-mrb-10"),
                AdminFormElement::columns([
                    [
                        AdminSection::getModel(BetsBankAccrual::class)->fireDisplay(['scopes' => ['user_id' => $id,]])
                    ],
                ])->setHtmlAttribute('class', "b-mrb-10"),
            ])->getButtons()->setButtons([]);

            $tabs->appendTab($additionalInformation, trans("admin/{$table}.tabs.AdditionalInfo"))->setIcon('<i class="fa fa-info"></i>');
            $tabs->appendTab($mainStats, trans("admin/{$table}.tabs.MainStats"))->setIcon('<i class="fa fa-credit-card"></i>')
                 ->setHtmlAttribute('class', "ajax_append")->setHtmlAttribute('data-type', "a-{$mainStats->type}")
                 ->setHtmlAttribute('data-id', "{$instance->id}");
            $tabs->appendTab($notes, trans("admin/{$table}.tabs.Notes"))->setIcon('<i class="fa fa-credit-card"></i>');
            $tabs->appendTab($userGateways, trans("admin/{$table}.tabs.Gateways"))->setIcon('<i class="fa fa-credit-card"></i>');
            $tabs->appendTab($operations, trans("admin/{$table}.tabs.Operations"))->setIcon('<i class="fa fa-credit-card"></i>');
            $tabs->appendTab($authorizations, trans("admin/{$table}.tabs.Authorizations"))->setIcon('<i class="fa fa-credit-card"></i>');
            $tabs->appendTab($gameHistory, trans("admin/{$table}.tabs.GameHistory"))->setIcon('<i class="fa fa-credit-card"></i>')
                 ->setHtmlAttribute('class', "ajax_append")->setHtmlAttribute('data-type', "a-{$gameHistory->type}")
                 ->setHtmlAttribute('data-id', "{$instance->id}");
            $tabs->appendTab($userStatusPoints, trans("admin/{$table}.tabs.StatusPoints"))->setIcon('<i class="fa fa-credit-card"></i>');
            $tabs->appendTab($bonuses, trans("admin/{$table}.tabs.Bonuses"))->setIcon('<i class="fa fa-credit-card"></i>');
            $tabs->appendTab($personalBonusLimits, trans("admin/{$table}.tabs.PersonalBonusLimits"))->setIcon('<i class="fa fa-credit-card"></i>');
            $tabs->appendTab($betsLimit, trans("admin/{$table}.tabs.BetsLimit"))->setIcon('<i class="fa fa-credit-card"></i>');
            $tabs->appendTab($riskEvents, trans("admin/{$table}.tabs.RiskEvents"))->setIcon('<i class="fa fa-credit-card"></i>');
            $tabs->appendTab($betsBankAccruals, trans("admin/{$table}.tabs.BetsBankAccruals"))->setIcon('<i class="fa fa-credit-card"></i>')
                ->setHtmlAttribute('class', "ajax_append")->setHtmlAttribute('data-type', "a-{$betsBankAccruals->type}")
                ->setHtmlAttribute('data-id', "{$instance->id}");
        }

        return $tabs;
    }
}
