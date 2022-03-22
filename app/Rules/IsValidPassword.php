<?php
namespace App\Rules;

use Illuminate\Support\Str;
use Illuminate\Contracts\Validation\Rule;

class IsValidPassword implements Rule{
      /**
     * Determine if the Length Validation Rule passes.
     *
     * @var boolean
     */
    public $lengthPasses = true;

    /**
     * Determine if the MixedCase Validation Rule passes.
     *
     * @var boolean
     */
    public $mixedCasePasses = true;

    /**
     * Determine if the Numeric Validation Rule passes.
     *
     * @var boolean
     */
    public $numericPasses = true;


    
    /**
     * Determine if the Special Character Validation Rule passes.
     *
     * @var boolean
     */
    public $specialCharacterPasses = true;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->lengthPasses = (Str::length($value) >= 8);
        $this->mixedCasePasses = ((bool) preg_match('/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u', $value));
        $this->numericPasses = ((bool) preg_match('/\pN/u', $value));
        $this->specialCharacterPasses = ((bool) preg_match('/\p{Z}|\p{S}|\p{P}/u', $value));

        return ($this->lengthPasses && $this->mixedCasePasses && $this->numericPasses && $this->specialCharacterPasses);
    }

    public function message()
    {
        return 'The :attribute must be at least 8 characters and contain at least one uppercase character, one lowercase character, one number, and one special character.';
    }
}