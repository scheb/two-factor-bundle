<?php

declare(strict_types=1);

namespace Scheb\TwoFactorBundle\Mailer;

use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

class SymfonyAuthCodeMailer implements AuthCodeMailerInterface
{
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $senderEmail;

    /**
     * @var string|null
     */
    private $senderName;

    public function __construct(Mailer $mailer, string $senderEmail, ?string $senderName)
    {
        $this->mailer = $mailer;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
    }

    public function sendAuthCode(TwoFactorInterface $user): void
    {
        $message = new Email();
        $message
            ->to($user->getEmailAuthRecipient())
            ->from($this->senderEmail)
            ->subject('Authentication Code')
            ->text($user->getEmailAuthCode())
        ;
        $this->mailer->send($message);
    }
}
