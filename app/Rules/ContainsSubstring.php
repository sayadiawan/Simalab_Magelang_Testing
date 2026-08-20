<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ContainsSubstring implements Rule
{
    protected $substring;

    public function __construct($substring)
    {
        $this->substring = $substring;
    }

    public function passes($attribute, $value)
    {
        return strpos($value, $this->substring) !== false;
    }

    public function message()
    {
        return 'The :attribute must contain ' . $this->substring . '.';
    }
}