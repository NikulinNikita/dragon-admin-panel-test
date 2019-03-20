<?php

namespace App\Providers;

use Admin\Display\Column\Image;
use Blade;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        // Replace SleepingOwl\Column\Image component with custom one
        \AdminColumn::bind('image', Image::class);

        Blade::setEchoFormat('nl2br(e(%s))');

        Blade::directive('TagBlock', function ($params) {
            return $this->getEchoString('tagBlock', $params);
        });

        Blade::directive('InputBlock', function ($params) {
            return $this->getEchoString('inputBlock', $params);
        });

        Blade::directive('ReplaceBlock', function ($params) {
            $cleanedParamsString = str_replace(' ', '', trim(preg_replace('/\s\s+/', ' ', $params)));
            $cleanedParamsArr    = explode(',[', $cleanedParamsString);
            $blockFullPath       = str_replace(['"', "'"], '', $cleanedParamsArr[0]);
            $blockPath           = explode('._', $blockFullPath);
            if (stristr($blockFullPath, '-id_', true)) {
                $blockFullPath = stristr($blockFullPath, '-id_', true);
            }
            $blockVars        = count($cleanedParamsArr) > 1 ? str_replace(['[', ']'], '', $cleanedParamsArr[1]) : '';
            $parametrizedVars = $blockVars ? $this->parametrizeVars($blockVars, 'stringify') : '';

            return '<div class="b-replaced_block b-replaced_block_' . $blockPath[1] . '">
				        <?php echo $__env->make("' . $blockFullPath . '", [' . $parametrizedVars . '], array_except(get_defined_vars(), array("__data", "__path")))->render(); ?>
				    </div>';
        });

        Blade::directive('AgentsTree', function ($agents) {
            return '<?php echo "' .print_r($agents, true). '";?>';
            if (is_array($agents)) {
                $html = $this->renderAgent($agents);

                return '<?php echo "' . $html . '"?>'; 
            }
        });
    }

    protected function getEchoString($type, $params)
    {
        if (strpos($params, 'type')) {
            $params           = substr(trim(preg_replace('/\s\s+/', ' ', $params)), 1, -1);
            $parametrizedVars = $this->parametrizeVars($params, 'b_counter');
        } else {
            $parametrizedVars = '';
        }

        return '<?php echo $__env->make("partials.mini._' . $type . 's", [$b_item = $b_counter++, ' . $parametrizedVars . '], array_except(get_defined_vars(), array("__data", "__path")))->render(); ?>';
    }

    protected function parametrizeVars($params, $method)
    {
        $paramsArr = explode(',', str_replace([',', ', '], ',', $params));
        foreach ($paramsArr as $k => $p) {
            $pArr                 = explode('=', str_replace([' =', '= ', ' = '], '=', $p));
            $pCheck               = explode('=', str_replace(['===', '=='], 'equal', $p));
            $transformVarsArr[$k] = $this->transformVarsByMethod($pArr, $pCheck, $k, $method);
        }
        $parametrizedVars = implode(', ', $transformVarsArr);

        return $parametrizedVars;
    }

    protected function transformVarsByMethod($pArr, $pCheck, $k, $method)
    {
        if ($method === 'b_counter') {
            if (count($pCheck) > 1 && strpos($pArr[0], '$') === 0) {
                $pArr[0] = substr_replace($pArr[0], '$b_', 0, 1) . '_{$b_item}';
            }
            $transformVar = implode('=', $pArr);
        } else if ($method === 'stringify') {
            if (count($pCheck) > 1 && strpos($pArr[0], '$') === 0) {
                $pArr[0] = "'" . str_replace('$', '', $pArr[0]) . "'";
            }
            $transformVar = implode('=>', $pArr);
        }

        return $transformVar;
    }

    protected function renderAgent($agents, $level = 1)
    {
        $html = '';
        $subHtml = '';

        foreach($agents as $agent) {
            if ($agent->children) {
                $subHtml .= $this->renderAgent($agent->children, $level + 1);
            }

            $html .= '<div class="agent-'. $level .'" style="padding-left: ' . ($level * 10) . 'px"><h4>' . $level . '</h4><div>' . $subHtml . '</div></div>';
        }

        return $html;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Passport::ignoreMigrations();
        if ($this->app->environment('local')) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
        $this->app->bind('path.public', function () {
            return base_path() . '/public';
        });
    }
}