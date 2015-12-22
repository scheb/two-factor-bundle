<?php

namespace Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator;

use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\PersisterInterface;

class CodeGenerator implements CodeGeneratorInterface
{
    /**
     * @var \Scheb\TwoFactorBundle\Model\PersisterInterface
     */
    private $persister;

    /**
     * @var \Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface
     */
    private $mailer;

    /**
     * Digit number of authentication code.
     *
     * @var int
     */
    private $digits;

    /**
     * Construct the code generator service.
     *
     * @param \Scheb\TwoFactorBundle\Model\PersisterInterface       $persister
     * @param \Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface $mailer
     * @param int                                                   $digits
     */
    public function __construct(PersisterInterface $persister, AuthCodeMailerInterface $mailer, $digits)
    {
        $this->persister = $persister;
        $this->mailer = $mailer;
        $this->digits = $digits;
    }

    /**
     * Generate a new authentication code an send it to the user.
     *
     * @param \Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface $user
     */
    public function generateAndSend(TwoFactorInterface $user)
    {
        $min = pow(10, $this->digits - 1);
        $max = pow(10, $this->digits) - 1;
        $code = $this->generateCode($min, $max);
        $user->setEmailAuthCode($code);
        $this->persister->persist($user);
        $this->mailer->sendAuthCode($user);
    }

    /**
     * Generate authentication code.
     *
     * @param int $min
     * @param int $max
     *
     * @return int
     */
    protected function generateCode($min, $max)
    {
        return mt_rand($min, $max);
    }
}
