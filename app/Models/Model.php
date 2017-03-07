<?php
namespace Jolt\Models;

use Jolt\Exceptions;

class Model extends \Eloquent
{
    /////////////////////////
    // Validation
    /////////////////////////
    public function Validate($data = null)
    {
        return \Validator::make($data??$this->attributes, $this->rules)->passes();
    }

    public function ValidationErrors($data = null)
    {
        $val = \Validator::make($data??$this->attributes, $this->rules);
        $val->passes();
        return $val->errors();
    }

    /////////////////////////
    // Laravel
    /////////////////////////

    /**
     * In general: configures static properties which would otherwise need to be set outside of the class.
     * This instance: automatically validate the model.
     */
    protected static function boot()
    {
        parent::boot();

        $validateModelOrFail = function($model) {
            if (!$model->Validate()) {
                throw new Exceptions\ModelValidation($model->ValidationErrors());
            }
        };

        static::creating($validateModelOrFail);
        static::updating($validateModelOrFail);
    }
}
