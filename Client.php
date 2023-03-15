<?php

namespace Tcgunel\AsistbtSms;

use RuntimeException, SoapClient, SoapFault;

class Client extends AbstractParameterInit
{
    public string $url;

    public array $options = [];

    public SoapClient $_client;

    public function __construct(array $parameters)
    {
        parent::__construct($parameters);

        try {

            $this->_client = new SoapClient($this->url, $this->options);

        } catch (SoapFault $e) {

            throw new RuntimeException($e->getMessage(), (int) $e->getCode(), $e);

        }
    }

    /**
     * @throws RuntimeException
     */
    public function __call(string $name, array $arguments)
    {
        try {

            return call_user_func_array([$this->_client, $name], $arguments);

        } catch (SoapFault $e) {

            throw new RuntimeException($e->getMessage(), (int) $e->getCode(), $e);

        }
    }
}