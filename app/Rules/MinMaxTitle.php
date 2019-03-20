<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MinMaxTitle implements Rule
{
    private $params;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        list($min, $max) = explode('-', $value);
        
        if (!$min || !$max) {
            return false;
        }

        $this->min = (int)$min;
        $this->max = (int)$max;
        
        if ((isset($this->params['min']) && $min) && ($this->params['min'] > $min)) {
            return false;
        }

        if ((isset($this->params['max']) && $max) && ($this->params['max'] < $max)) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '<p>The title should be contain minimum and maximum limit in the format min-max(Example: 1-100)</p>
                <p>The minimum allowable value - ' . $this->params['min'] . '</p>
                <p>The maximum allowable value - ' . $this->params['max'] . '</p>';

    }
}
