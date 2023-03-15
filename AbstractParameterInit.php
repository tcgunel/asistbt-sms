<?php

namespace Tcgunel\AsistbtSms;

class AbstractParameterInit
{
    public function __construct(array $parameters)
    {
        array_walk($parameters, function ($parameter, $key){
            $this->$key = $parameter;
        });
    }
}