# Mailgun wrapper based on Devorto mailer interface.
Send mails using mailgun, uses: [devorto/mail](https://github.com/devorto/mail)

## Usage
```php
<?php

use Devorto\Mail\Mail;
use Devorto\Mail\Recipient;
use Devorto\MailMailgun\Mailer;

require_once __DIR__ . '/../vendor/autoload.php';

$message = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Test mail</title>
</head>
<body>
    This is a test email, and you cannot respond to it :)
</body>
</html>
HTML;

$mail = (new Mail())
    ->setTo(new Recipient('info@devorto.com', 'Info'))
    ->setFrom(new Recipient('no-reply@devorto.com', 'NoReply'))
    ->setSubject('Test mail')
    ->setMessage($message);

(new Mailer('mailgun-api-key', 'mailgun-api-url', 'mailgun-api-domain'))
    ->send($mail);

```
