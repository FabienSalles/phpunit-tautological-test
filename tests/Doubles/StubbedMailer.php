<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

final class StubbedMailer implements MailerInterface
{
    /** @var list<Email> */
    public array $sentEmails = [];

    public function send(RawMessage $message, ?Envelope $envelope = null): void
    {
        if ($message instanceof Email) {
            $this->sentEmails[] = $message;
        }
    }

    public function lastEmail(): ?Email
    {
        return $this->sentEmails[array_key_last($this->sentEmails)] ?? null;
    }
}
