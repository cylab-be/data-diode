<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Netmask implements Rule
{
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
        return preg_match("/^(((128|192|224|240|248|252|254)\\.0\\.0\\.0)|"
            . "(255\\.(0|128|192|224|240|248|252|254)\\.0\\.0)|(255\\.255\\.(0|128|192|224|240|248|252|254)"
            . "\\.0)|(255\\.255\\.255\\.(0|128|192|224|240|248|252|254)))$/", $value) === 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid netmask';
    }
}
