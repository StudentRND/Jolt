<?php
namespace Jolt\Exceptions;

/**
 * Thrown when model validation fails.
 */
class ModelValidation extends \Exception
{
    public $errors;

    public function __construct($errors)
    {
        $this->errors = $errors;

        $this->message = "";
        foreach ($errors->all() as $error) {
            $this->message .= "$error\n";
        }
    }
}
