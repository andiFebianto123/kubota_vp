<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MultipleMailVendorRule implements Rule
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
        $isValid = false;
        if (str_contains($value, '@')) { 
            $isValid = true;
        }
        if (str_contains($value, ',')) { 
            $intSuccess = 0;
            $isValid = false;
            $emails = explode(",",$value);

            foreach ($emails as $key => $email) {
                if (str_contains($email, '@')) {
                    $intSuccess++;
                }
            }
            if (sizeof($emails) == $intSuccess) {
                $isValid = true;
            }
        }
        return $isValid;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute format is not valid';
    }
}
