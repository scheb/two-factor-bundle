<?php

declare(strict_types=1);

namespace Scheb\TwoFactorBundle\Tests\Mailer;

use PHPUnit\Framework\MockObject\MockObject;
use Scheb\TwoFactorBundle\Mailer\SymfonyAuthCodeMailer;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Tests\TestCase;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;

class SymfonyAuthCodeMailerTest extends TestCase
{
    /**
     * @var MockObject|TransportInterface
     */
    private $symfonyTransportInterface;

    /**
     * @var SymfonyAuthCodeMailer
     */
    private $mailer;

    protected function setUp(): void
    {
        if (\interface_exists(TransportInterface::class) === false) {
            $this->markTestSkipped("Symfony mailer not installed");
            return;
        }
        $this->symfonyTransportInterface = $this->createMock(TransportInterface::class);
        $this->mailer = new SymfonyAuthCodeMailer(new Mailer($this->symfonyTransportInterface), 'sender@example.com', 'Sender Name');
    }

    /**
     * @test
     */
    public function sendAuthCode_withSymfonyMailer_sendEmail(): void
    {
        //Stub the user object
        $user = $this->createMock(TwoFactorInterface::class);
        $user
            ->expects($this->any())
            ->method('getEmailAuthRecipient')
            ->willReturn('recipient@example.com');
        $user
            ->expects($this->any())
            ->method('getEmailAuthCode')
            ->willReturn('1234');

        $messageValidator = function ($mail) {
            /* @var Email $mail */
            $this->assertInstanceOf(Email::class, $mail);
            $this->assertEquals('recipient@example.com', current($mail->getTo())->getAddress());
            $this->assertEquals('sender@example.com', current($mail->getFrom())->getAddress());
            $this->assertEquals('Authentication Code', $mail->getSubject());
            $this->assertEquals('1234', $mail->getBody()->bodyToString());

            return true;
        };

        //Expect mail to be sent
        $this->symfonyTransportInterface
            ->expects($this->once())
            ->method('send')
            ->with($this->callback($messageValidator));

        $this->mailer->sendAuthCode($user);
    }
}
