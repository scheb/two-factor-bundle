<?php
namespace Scheb\TwoFactorBundle\Model;

interface TrustedComputerInterface
{

    /**
     * Add a trusted computer token
     *
     * @param string    $token
     * @param \DateTime $validUntil
     */
    public function addTrustedComputer($token, \DateTime $validUntil, \Symfony\Component\HttpFoundation\HeaderBag $headers);

    /**
     * Validate a trusted computer token
     *
     * @param  string  $token
     * @return boolean
     */
    public function isTrustedComputer($token);
}
