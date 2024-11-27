<?php

namespace Devorto\MailMailgun;

use Devorto\Mail\Attachment;
use Devorto\Mail\Mail;
use Devorto\Mail\Mailer as MailerInterface;
use Devorto\Mail\Recipient;
use InvalidArgumentException;
use Mailgun\Mailgun;
use RuntimeException;
use Throwable;

/**
 * Class Mailer
 *
 * @package Duracom\MailMailgun
 */
class Mailer implements MailerInterface
{
    /**
     * @var Mailgun
     */
    protected Mailgun $mailer;

    /**
     * @var string
     */
    protected string $domain;

    /**
     * Mailer constructor.
     *
     * @param string $apiKey
     * @param string $apiUrl
     * @param string $domain
     */
    public function __construct(string $apiKey, string $apiUrl, string $domain)
    {
        $this->mailer = Mailgun::create($apiKey, $apiUrl);
        $this->domain = $domain;
    }

    /**
     * @param Mail $mail
     *
     * @return void
     */
    public function send(Mail $mail): void
    {
        if (empty($mail->getTo())) {
            throw new InvalidArgumentException('No "to" address provided.');
        }

        if (empty($mail->getFrom())) {
            throw new InvalidArgumentException('No "from" address provided.');
        }

        if (empty($mail->getSubject())) {
            throw new InvalidArgumentException('No "subject" provided.');
        }

        if (empty($mail->getMessage())) {
            throw new InvalidArgumentException('No "message" provided.');
        }

        $data = [
            'to' => implode(',', array_map([static::class, 'renderRecipient'], $mail->getTo())),
            'from' => static::renderRecipient($mail->getFrom()),
            'subject' => $mail->getSubject(),
            'html' => $mail->getMessage()
        ];

        if (!empty($mail->getReplyTo())) {
            $data['h:Reply-To'] = static::renderRecipient($mail->getReplyTo());
        }

        if (!empty($mail->getCc())) {
            $data['cc'] = implode(',', array_map([static::class, 'renderRecipient'], $mail->getCc()));
        }

        if (!empty($mail->getBcc())) {
            $data['bcc'] = implode(',', array_map([static::class, 'renderRecipient'], $mail->getBcc()));
        }

        if (!empty($mail->getAttachments())) {
            $data['attachment'] = array_map([static::class, 'renderAttachments'], $mail->getAttachments());
        }

        try {
            $this->mailer->messages()->send($this->domain, $data);
        } catch (Throwable $throwable) {
            throw new RuntimeException('Sending email failed.', 0, $throwable);
        }
    }

    /**
     * @param Recipient $recipient
     *
     * @return string
     */
    protected static function renderRecipient(Recipient $recipient): string
    {
        if (empty($recipient->getName())) {
            return $recipient->getEmail();
        }

        return sprintf('%s <%s>', $recipient->getName(), $recipient->getEmail());
    }

    /**
     * @param Attachment $attachment
     *
     * @return array
     */
    protected static function renderAttachments(Attachment $attachment): array
    {
        return ['fileContent' => $attachment->getContent(), 'filename' => $attachment->getName()];
    }
}
