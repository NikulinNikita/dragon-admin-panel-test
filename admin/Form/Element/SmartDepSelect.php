<?php

namespace Admin\Form\Element;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Router;
use SleepingOwl\Admin\Contracts\WithRoutesInterface;
use SleepingOwl\Admin\Exceptions\Form\Element\SelectException;

class SmartDepSelect extends SmartSelect implements WithRoutesInterface
{
    protected $dataUrl = '';
    protected $dataDepends = [];
    protected $params;
    protected $view = 'admin::form.element.smartDepSelect';
    protected $parentModel;
    protected $depTarget;
    protected $depKey;
    protected $unlocked;

    public function __construct($path, $label = null, array $depends = [], Model $parentModel = null)
    {
        $label = $this->getSmartLabel($path, $label);
        parent::__construct($path, $label, []);

        $this->setDataDepends($depends);
        if ( ! $parentModel) {
            $this->setOptions([]);
        } else {
            $this->setParentModel($parentModel);
            $this->setModelForOptions(false);
        }
    }

    public static function registerRoutes(Router $router)
    {
        $routeName = 'admin.getDependentSelectData';

        if ( ! $router->has($routeName)) {
            $router->post('getDependentSelectData', [
                'as' => $routeName,
                function () {
                    return new JsonResponse(['output' => [], 'selected' => null,]);
                },
            ]);
        }
    }

    public function setModelForOptions($modelForOptions)
    {
        $depValue    = array_first(json_decode($this->getDataDepends()));
        $parentModel = $this->getParentModel();

        if ($modelForOptions === false && $parentModel) {
            $modelName       = strpos($depValue, '_type') ? $parentModel->{$depValue} : str_replace(['_id'], '', $depValue);
            $model           = BaseModel::makeModelClass($modelName);
            $modelForOptions = app($model);
        } else if (is_string($modelForOptions)) {
            $modelForOptions = app($modelForOptions);
        } else {
            $modelForOptions = app($parentModel);
        }

        if ( ! ($modelForOptions instanceof Model)) {
            throw new SelectException('Class must be instanced of Illuminate\Database\Eloquent\Model');
        }

        $this->modelForOptions = $modelForOptions;

        return $this;
    }

    public function getDepTarget()
    {
        return $this->depTarget;
    }

    public function setDepTarget($depTarget)
    {
        $this->depTarget = $depTarget;

        return $this;
    }

    public function getDepKey()
    {
        return $this->depKey;
    }

    public function setDepKey($depKey)
    {
        $this->depKey = $depKey;

        return $this;
    }

    public function getUnlocked()
    {
        return $this->unlocked;
    }

    public function setUnlocked($unlocked)
    {
        $this->unlocked = $unlocked ? 1 : 0;

        return $this;
    }

    public function getParentModel()
    {
        return $this->parentModel;
    }

    public function setParentModel($parentModel)
    {
        $this->parentModel = $parentModel;

        return $this;
    }

    public function hasDependKey($key)
    {
        return array_has($this->params, $key);
    }

    public function getDependValue($key)
    {
        return array_get($this->params, $key, $this->getModel()->getAttribute($key));
    }

    public function getDataUrl()
    {
        return route('admin.getDependentSelectData') ??
               ($this->dataUrl ?:
                   route('admin.form.element.dependent-select', [
                       'adminModel' => \AdminSection::getModel($this->model)->getAlias(),
                       'field'      => $this->getName(),
                       'id'         => $this->model->getKey(),
                   ]));
    }

    public function setDataUrl($dataUrl)
    {
        $this->dataUrl = $dataUrl;

        return $this;
    }

    public function getDataDepends()
    {
        return json_encode($this->dataDepends);
    }

    public function setDataDepends($depends)
    {
        $this->dataDepends = is_array($depends) ? $depends : func_get_args();

        return $this;
    }

    public function setAjaxParameters(array $params)
    {
        $this->params = $params;

        return $this;
    }

    public function toArray()
    {
        $this->setHtmlAttributes([
            'data-url'        => $this->getDataUrl(),
            'data-depends'    => $this->getDataDepends(),
            'class'           => 'form-control input-select js-data-smartSelect js-data-smartDepSelect',
            'size'            => 1,
            'model'           => $this->getModelForOptions() ? get_class($this->getModelForOptions()) : null,
            'isStaticOptions' => false,
            'depKey'          => $this->getDepKey(),
            'depTarget'       => $this->getDepTarget(),
            'unlocked'        => $this->getUnlocked(),
        ]);

        return ['attributes' => $this->getHtmlAttributes() + SmartSelect::toArray()['attributes']] + SmartSelect::toArray();
    }
}
