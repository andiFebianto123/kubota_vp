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

        $intAllowNext = 0;
        $allowNext = false;

        if ($this->lengthPasses) {
            $intAllowNext++;
        }
        if ($this->mixedCasePasses) {
            $intAllowNext++;
        }
        if ($this->numericPasses) {
            $intAllowNext++;
        }
        if ($this->specialCharacterPasses) {
            $intAllowNext++;
        }

        if ($intAllowNext == 4) {
            $allowNext = true;
        }

        return $allowNext;
    }

    public function message()
    {
        $arrParamPass = [];
        $strParamPass = "at least ";
        if (!$this->lengthPasses) {
           $arrParamPass[] = "8 characters";
        }
        if (!$this->mixedCasePasses) {
            $arrParamPass[] = "one uppercase character, one lowercase character";
        }
        if (!$this->numericPasses) {
            $arrParamPass[] = "one number";
        }
        if (!$this->specialCharacterPasses) {
            $arrParamPass[] = "one special character";
        }

        $n = 1;
        foreach ($arrParamPass as $app) {
            if (sizeof($arrParamPass) == $n && $n > 1 ) {
                $strParamPass .= 'and '. $app.'';
            }else{
                $strParamPass .= $app.', ';
            }
            $n++;
        }
        
        return 'The :attribute must be contain '.$strParamPass;
        // return 'The :attribute must be at least 8 characters and contain at least one uppercase character, one lowercase character, one number, and one special character.';
    }
}