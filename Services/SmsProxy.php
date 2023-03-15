<?php

namespace Tcgunel\AsistbtSms\Services;

use Tcgunel\AsistbtSms\AbstractParameterInit;
use Tcgunel\AsistbtSms\Client;
use Tcgunel\AsistbtSms\Utils\Array2XML;
use Tcgunel\AsistbtSms\Exceptions\AsistException;

/**
 * Class SmsProxy
 * Document https://dosya.asistbt.com.tr/SmsProxy.pdf
 * @package Tcgunel\AsistbtSms\service
 */
class SmsProxy extends AbstractParameterInit
{
    public string $url = 'https://webservice.asistiletisim.com.tr/SmsProxy.asmx?wsdl';

    public Client $client;

    /**
     * Sistemde tanımlı olan kullanıcı kodunuz.
     */
    public int $user_code;

    /**
     * Sistemde tanımlı olan kullanıcı adınız.
     */
    public string $username;

    /**
     * : Sistemde tanımlı olan şifreniz.
     */
    public string $password;

    /**
     * Sistemde tanımlı olan kullanıcınızın hesap kodu.
     */
    public int $account_id;

    /**
     * Sistemde tanımlı olan kullanıcı başlığı.
     * Max. 11 karakter uzunluğunda olabilir.
     * @var string
     */
    public ?string $originator;

    private array $_receiverList = [];

    private array $_messageText = [];

    /**
     * Blacklist kontrolü gerçekleştirilmektedir.
     */
    private bool $_isCheckBlackList = true;

    /**
     * Mesaj geçerlilik süresi. Mesajların alıcılara gönderiminin denenmesini
     * istediğiniz süreyi belirlemek için kullanılır.
     * Bulk hesaplarda dakika cinsinden max 3360 OTP hesaplarda saniye cinsinden max 300 olarak belirtilebilir
     */
    private int $_validityPeriod = 60;

    /**
     * İleri tarihli gönderim gerçekleştirmek için tarih formatı ddMMyyHHmmss
     * şeklinde girilmelidir. Default olarak gönderim anlık gerçekleştirileceğinden opsiyonel
     * olarak boş bırakılabilir.
     */
    private ?string $_sendDate;

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
     * @param string|array $receiver
     */
    public function addReceiver($receiver): self
    {
        if(is_array($receiver)){
            foreach ($receiver as $item) {
                $this->_receiverList[] = $item;
            }
        }else{
            $this->_receiverList[] = $receiver;
        }
        return $this;
    }

    /**
     * @param string|array $message
     * @return $this
     */
    public function setMessage($message): self
    {
        if(is_array($message)){
            foreach ($message as $item) {
                $this->_messageText[] = $item;
            }
        }else{
            $this->_messageText[] = $message;
        }
        return $this;
    }

    public function setOriginator(string $originator): self
    {
        $this->originator = $originator;
        return $this;
    }

    public function setValidityPeriod(int $validityPeriod): self
    {
        $this->_validityPeriod = $validityPeriod;
        return $this;
    }

    public function setSendDate(string $sendDate): self
    {
        $this->_sendDate = $sendDate;
        return $this;
    }

    public function setIsCheckBlackList(bool $isCheckBlackList): self
    {
        $this->_isCheckBlackList = $isCheckBlackList;
        return $this;
    }

    /**
     * Bilgilerini girdiğiniz kullanıcı hesabı sistem tarafından kontrol edilir,
     * cevap olarak tanımlı hesap prepaid (kredili) ise güncel kredi adedi bilgisi sorgulanır.
     * @throws \DOMException
     * @throws AsistException
     * @throws \JsonException
     */
    public function getCredit(): array
    {
        $response = $this->client->getCredit([
            'requestXml' => Array2XML::convert([
                'Username' => $this->username,
                'Password' => $this->password,
                'UserCode' => $this->user_code,
                'AccountId' => $this->account_id,
            ],'GetCredit')
        ]);

        if(($errorCode = data_get($response,'getCreditResult.ErrorCode')) && (int)$errorCode !== 0){
            throw new AsistException($errorCode);
        }

        return json_decode(json_encode($response, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }


    /**
     * Bilgilerini girdiğiniz kullanıcı hesabı sistem tarafından kontrol edilir,
     * cevap olarak kullanıcıya tanımlı olan Originator (Alfanumeric Sender) bilgileri sorgulanır.
     * @throws \DOMException
     * @throws AsistException|\JsonException
     */
    public function getOriginator(): array
    {
        $response = $this->client->getOriginator([
            'requestXml' => Array2XML::convert([
                'Username' => $this->username,
                'Password' => $this->password,
                'UserCode' => $this->user_code,
                'AccountId' => $this->account_id,
            ],'GetOriginator')
        ]);

        if(($errorCode = data_get($response,'getOriginatorResult.ErrorCode')) && (int)$errorCode !== 0){
            throw new AsistException($errorCode);
        }

        return json_decode(json_encode($response, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }


    /**
     * SendSms Fonksiyonu, uygulama geliştiricilerin hazırlamış oldukları Kısa Mesajları (SMS),
     * sunucuya teslim edebilmelerini sağlayan gönderim fonksiyonudur.
     * @throws AsistException
     * @throws \DOMException
     * @throws \JsonException
     */
    public function sendSms(): array
    {
        $sendOptions = [
            'Username' => $this->username,
            'Password' => $this->password,
            'UserCode' => $this->user_code,
            'AccountId' => $this->account_id,
            'Originator' => $this->originator,
            'SendDate' => $this->_sendDate ?? null,
            'ValidityPeriod' => $this->_validityPeriod,
            'IsCheckBlackList' => $this->_isCheckBlackList === true ? 1 : 0,
            'ReceiverList' => []
        ];

        foreach ($this->_receiverList as $item) {
            $sendOptions['ReceiverList']['Receiver'][] = $item;
        }

        if(count($this->_messageText) > 1){
            $sendOptions['MessageText'] = '[##MESAJ##]';

            foreach ($this->_messageText as $item) {
                $sendOptions['PersonalMessages']['PersonalMessage'][] = ['Parameter' => $item];
            }

        }else{
            $sendOptions['MessageText'] = $this->_messageText[0];
        }

        $response = $this->client->sendSms([
            'requestXml' => Array2XML::convert($sendOptions,'SendSms')
        ]);

        if(($errorCode = data_get($response, 'sendSmsResult.ErrorCode')) && (int)$errorCode !== 0){
            throw new AsistException($errorCode);
        }

        return json_decode(json_encode($response, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * İleri tarihli bir gönderimin iptal edilmesi için kullanılır. İleri tarihli gönderime ait PacketId değeri,
     * ilgili metod ile kullanılarak gönderim iptali sağlanır.
     * @throws \DOMException
     * @throws AsistException
     * @throws \JsonException
     */
    public function abortSms(int $packet_id): array
    {
        $response = $this->client->abortSms([
            'requestXml' => Array2XML::convert([
                'Username' => $this->username,
                'Password' => $this->password,
                'UserCode' => $this->user_code,
                'AccountId' => $this->account_id,
                'PacketId' => $packet_id,
            ],'AbortSms')
        ]);

        if(($errorCode = data_get($response,'abortSmsResult.ErrorCode')) && (int)$errorCode !== 0){
            throw new AsistException($errorCode);
        }

        return json_decode(json_encode($response, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * getStatus fonksiyonu bir SMS gönderimine ait özet raporunun ya da
     * gsm bazlı durum raporlarının sorgulanması için kullanılır.
     * @throws AsistException
     * @throws \DOMException
     * @throws \JsonException
     */
    public function getStatusByPacketId(int $packet_id): array
    {
        $response = $this->client->getStatus([
            'requestXml' => Array2XML::convert([
                'Username' => $this->username,
                'Password' => $this->password,
                'UserCode' => $this->user_code,
                'AccountId' => $this->account_id,
                'PacketId' => $packet_id,
            ],'GetStatus')
        ]);

        if(($errorCode = data_get($response,'getStatusResult.ErrorCode')) && (int)$errorCode !== 0){
            throw new AsistException($errorCode);
        }

        return json_decode(json_encode($response, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * getStatus fonksiyonu bir SMS gönderimine ait özet raporunun ya da
     * gsm bazlı durum raporlarının sorgulanması için kullanılır.
     * @throws AsistException
     * @throws \DOMException
     * @throws \JsonException
     */
    public function getStatusByMessageId(array $message_id_list = []): array
    {
        $sendOptions = [
            'Username' => $this->username,
            'Password' => $this->password,
            'UserCode' => $this->user_code,
            'AccountId' => $this->account_id,
        ];

        foreach ($message_id_list as $item) {
            $sendOptions['MessageIdList']['MessageId'][] = $item;
        }

        $response = $this->client->getStatus([
            'requestXml' => Array2XML::convert($sendOptions,'GetStatus')
        ]);

        if(($errorCode = data_get($response,'getStatusResult.ErrorCode')) && (int)$errorCode !== 0){
            throw new AsistException($errorCode);
        }

        return json_decode(json_encode($response, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }
}