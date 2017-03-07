<?php
namespace Jolt\Exceptions;

class UserInput extends \Exception
{
    public function __construct($message) {
        parent::__construct($message);
    }
}
