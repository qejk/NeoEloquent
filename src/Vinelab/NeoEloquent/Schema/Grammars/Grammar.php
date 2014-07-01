<?php namespace Vinelab\NeoEloquent\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\Grammar as IlluminateSchemaGrammar;

class Grammar extends IlluminateSchemaGrammar
{

    /**
     * Make sure the label is wrapped with backticks
     *
     * @param  string $label
     * @return string
     */
    public function wrapLabel($label)
    {
        // every label must begin with a ':' so we need to check
        // and reformat if need be.
        return trim(':`'. preg_replace('/^:/', '', $label) .'`');
    }

    /**
     * Turn a string into a valid property for a query.
     *
     * @param  string $property
     * @return string
     */
    public function propertize($property)
    {
        // Sanitize the string from all characters except alpha numeric.
        return preg_replace('[^A-Za-z0-9]', '', $property);
    }
}

