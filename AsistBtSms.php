<?php

namespace Tcgunel\AsistbtSms;

use Tcgunel\AsistbtSms\Services\ContactService;
use Tcgunel\AsistbtSms\Services\SmsProxy;

class AsistBtSms extends AbstractParameterInit
{
    public int $user_code;

    public string $username;

    public string $password;

    public int $account_id;

    public ?string $originator;

    public function smsProxy(array $options = []): SmsProxy
    {
        $options = array_merge([
            'user_code'  => $this->user_code,
            'username'   => $this->username,
            'password'   => $this->password,
            'account_id' => $this->account_id,
            'originator' => $this->originator ?? null,
        ], $options);

        return new SmsProxy($options);
    }

    public function contactService(array $options = []): ContactService
    {
        $options = array_merge([
            'user_code'  => $this->user_code,
            'username'   => $this->username,
            'password'   => $this->password,
            'account_id' => $this->account_id,
        ], $options);

        return new ContactService($options);
    }
}
