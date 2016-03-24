<?php
namespace Scheb\TwoFactorBundle\Tests\Security\TwoFactor\EventListener;

use Scheb\TwoFactorBundle\Security\TwoFactor\EventListener\InteractiveLoginListener;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;

class InteractiveLoginListenerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $authHandler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\EventListener\InteractiveLoginListener
     */
    private $listener;

    public function setUp()
    {
        $this->authHandler = $this->getMock("Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface");

        $supportedTokens = array("Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken");
        $this->listener = new InteractiveLoginListener($this->authHandler, $supportedTokens);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createEvent($token)
    {
        $this->request = $this->getMock("Symfony\Component\HttpFoundation\Request");
        $event = $this->getMockBuilder("Symfony\Component\Security\Http\Event\InteractiveLoginEvent")
            ->disableOriginalConstructor()
            ->getMock();
        $event
            ->expects($this->any())
            ->method("getRequest")
            ->will($this->returnValue($this->request));
        $event
            ->expects($this->any())
            ->method("getAuthenticationToken")
            ->will($this->returnValue($token));

        return $event;
    }

    /**
     * @test
     */
    public function onSecurityInteractiveLogin_tokenClassSupported_beginAuthentication()
    {
        $token = new UsernamePasswordToken("user", array(), "key");
        $event = $this->createEvent($token);

        //Expect TwoFactorProvider to be called
        $expectedContext = new AuthenticationContext($this->request, $token);
        $this->authHandler
            ->expects($this->once())
            ->method("beginAuthentication")
            ->with($expectedContext);

        $this->listener->onSecurityInteractiveLogin($event);
    }

    /**
     * @test
     */
    public function onSecurityInteractiveLogin_tokenClassNotSupported_doNothing()
    {
        $token = $this->getMock("Symfony\Component\Security\Core\Authentication\Token\TokenInterface");
        $event = $this->createEvent($token);

        //Expect TwoFactorProvider not to be called
        $this->authHandler
            ->expects($this->never())
            ->method("beginAuthentication");

        $this->listener->onSecurityInteractiveLogin($event);
    }

    /**
     * @test
     */
    public function onSecurityInteractiveLogin_exclude_pattern_doNothing()
    {
        $excludedPattern = '^/(admin)/';
        $this->authHandler = $this->getMock("Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationHandlerInterface");
        $supportedTokens = array("Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken");
        $this->listener = new InteractiveLoginListener($this->authHandler, $supportedTokens, $excludedPattern);

        $token = new UsernamePasswordToken("user", array(), "key");
        $event = $this->createEventWithExcludedPattern('/admin/login_check', $token);

        //Expect TwoFactorProvider not to be called
        $this->authHandler
            ->expects($this->never())
            ->method("beginAuthentication");
        
        $this->listener->onSecurityInteractiveLogin($event);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createEventWithExcludedPattern($pathInfo = "/some-path/", $token)
    {
        $this->request = $this->getMock("Symfony\Component\HttpFoundation\Request");
        $this->request
            ->expects($this->any())
            ->method("getPathInfo")
            ->will($this->returnValue($pathInfo));

        $event = $this->getMockBuilder("Symfony\Component\Security\Http\Event\InteractiveLoginEvent")
            ->disableOriginalConstructor()
            ->getMock();
        $event
            ->expects($this->any())
            ->method("getRequest")
            ->will($this->returnValue($this->request));
        $event
            ->expects($this->any())
            ->method("getAuthenticationToken")
            ->will($this->returnValue($token));

        return $event;
    }

    public function tearDown()
    {
        $this->listener = null;
        $this->authHandler = null;
        $this->request = null;
    }

}
