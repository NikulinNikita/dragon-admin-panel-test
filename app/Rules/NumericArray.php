<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NumericArray implements Rule
{
    private $incorectValues = [];
    private $defaultMessage = 'Must be a number.';
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        
        foreach($value as $index => $num) {
            if (!is_numeric($num)) {
                $this->incorectValues[$index] = $this->defaultMessage;
            }
        }

        return ($this->incorectValues) ? false : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->incorectValues;
    }
}
