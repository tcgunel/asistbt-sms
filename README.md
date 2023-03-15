Asist BT SMS
=================
Asist BT SMS Service Component

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Run

```
composer require tcgunel/asistbt-sms
```

Send Sms
-----
```php
try {
    
    $asistBtSms = new \Tcgunel\AsistbtSms\AsistBtSms([
        'user_code'  => (int)0000,
        'username'   => 'username',
        'password'   => 'password',
        'account_id' => (int)0000,
        'originator' => 'ORIGINATOR',
    ]);

    $response = $asistBtSms->smsProxy()
        ->addReceiver(['905554443322'])
        ->setMessage(['Message1 text'])
        //->setSendDate('150323001020') // ddMMyyHHmmss
        // For bulk messages parameter is in minutes. Max 3360.
        // For OTP messages parameter is in seconds. Max 300.
        //->setValidityPeriod(60)
        //->setIsCheckBlackList(true)
        ->sendSms();

} catch (AsistException $e) {
        
    $e->getMessage();

}
```

Get Credit
-----
```php
try {
    
    $asistBtSms = new \Tcgunel\AsistbtSms\AsistBtSms([
        'user_code'  => (int)0000,
        'username'   => 'username',
        'password'   => 'password',
        'account_id' => (int)0000,
    ]);

    $response = $asistBtSms->smsProxy()->getCredit();

} catch (AsistException $e) {
        
    $e->getMessage();

}
```

Get Originator
-----
```php
try {
    
    $asistBtSms = new \Tcgunel\AsistbtSms\AsistBtSms([
        'user_code'  => (int)0000,
        'username'   => 'username',
        'password'   => 'password',
        'account_id' => (int)0000,
    ]);

    $response = $asistBtSms->smsProxy()->getOriginator();

} catch (AsistException $e) {
        
    $e->getMessage();

}
```

Abort SMS
-----
```php
try {
    
    $asistBtSms = new \Tcgunel\AsistbtSms\AsistBtSms([
        'user_code'  => (int)0000,
        'username'   => 'username',
        'password'   => 'password',
        'account_id' => (int)0000,
    ]);

    $response = $asistBtSms->smsProxy()->abortSms((int)$packet_id); // $packet_id returns from sendSms.

} catch (AsistException $e) {
        
    $e->getMessage();

}
```

Get Status by Packet Id
-----
```php
try {
    
    $asistBtSms = new \Tcgunel\AsistbtSms\AsistBtSms([
        'user_code'  => (int)0000,
        'username'   => 'username',
        'password'   => 'password',
        'account_id' => (int)0000,
    ]);

    $response = $asistBtSms->smsProxy()->getStatusByPacketId((int)$packet_id); // $packet_id returns from sendSms.

} catch (AsistException $e) {
        
    $e->getMessage();

}
```

Get Status by Message Id
-----
```php
try {
    
    $asistBtSms = new \Tcgunel\AsistbtSms\AsistBtSms([
        'user_code'  => (int)0000,
        'username'   => 'username',
        'password'   => 'password',
        'account_id' => (int)0000,
    ]);

    $response = $asistBtSms->smsProxy()->getStatusByMessageId([1,2,3]); // Message_id returns from sendSms.

} catch (AsistException $e) {
        
    $e->getMessage();

}
```

Get Contact
-----
```php
try {
    
    $asistBtSms = new \Tcgunel\AsistbtSms\AsistBtSms([
        'user_code'  => (int)0000,
        'username'   => 'username',
        'password'   => 'password',
        'account_id' => (int)0000,
    ]);

    $response = $asistBtSms->contactService()->getContact((int)5554443322);

} catch (AsistException $e) {
        
    $e->getMessage();

}
```

Add Contact
-----
```php
try {
    
    $asistBtSms = new \Tcgunel\AsistbtSms\AsistBtSms([
        'user_code'  => (int)0000,
        'username'   => 'username',
        'password'   => 'password',
        'account_id' => (int)0000,
    ]);

    $response = $asistBtSms
        ->contactService()
        ->addContact(
            'Ad',
            'Soyad',
            (int)5554443322,
            'GROUPID',
            false, // isBlackList
        );

} catch (AsistException $e) {
        
    $e->getMessage();

}
```