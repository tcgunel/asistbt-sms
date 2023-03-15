<?php

namespace Tcgunel\AsistbtSms\Services;

use Tcgunel\AsistbtSms\AbstractParameterInit;
use Tcgunel\AsistbtSms\Client;
use Tcgunel\AsistbtSms\Exceptions\AsistException;
use Tcgunel\AsistbtSms\Utils\Array2XML;

/**
 * Class ContactService
 * @package mhunesi\sms\providers\asist\service
 */
class ContactService extends AbstractParameterInit
{
    /**
     * @var string
     */
    public $url = 'https://webservice.asistiletisim.com.tr/ContactService.asmx?wsdl';

    /**
     * @var Client
     *
     */
    public $client;

    /**
     * @var string
     */
    public $user_code;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $account_id;

    /**
     * Client init
     */
    public function __construct(array $parameters)
    {
        parent::__construct($parameters);

        $this->client = new Client([
            'url' => $this->url,
            'options' => [
                'trace' => true,
                'soap_version' => SOAP_1_1,
                'cache_wsdl' => 1,
            ]
        ]);
    }

    /**
     * @throws AsistException
     * @throws \DOMException
     * @throws \JsonException
     */
    public function getContact(int $receiver): array
    {
        $response = $this->client->getContact([
            'requestXml' => Array2XML::convert([
                'Username' => $this->username,
                'Password' => $this->password,
                'UserCode' => $this->user_code,
                'Receiver' => $receiver,
            ],'GetContact')
        ]);

        if(($errorCode = data_get($response,'getContactResult.ErrorCode')) && (int)$errorCode !== 0){
            throw new AsistException($errorCode);
        }

        return json_decode(json_encode($response, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws \DOMException
     * @throws AsistException
     * @throws \JsonException
     */
    public function addContact(string $name, string $surname, int $receiver, ?string $groupId = null, ?bool $isBlackList = false): array
    {
        $response = $this->client->addContact([
            'requestXml' => Array2XML::convert([
                'Username' => $this->username,
                'Password' => $this->password,
                'UserCode' => $this->user_code,
                'Name' => $name,
                'Surname' => $surname,
                'GroupId' => $groupId,
                'Receiver' => $receiver,
                'IsBlackList' => (int) $isBlackList,
            ],'AddContact')
        ]);

        if(($errorCode = data_get($response,'addContactResult.ErrorCode')) && (int)$errorCode !== 0){
            throw new AsistException($errorCode);
        }

        return json_decode(json_encode($response, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

}