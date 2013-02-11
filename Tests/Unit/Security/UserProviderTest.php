<?php

namespace Oro\Bundle\UserBundle\Tests\Security;

use Oro\Bundle\UserBundle\Security\UserProvider;

class UserProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $userManager;

    /**
     * @var UserProvider
     */
    private $userProvider;

    protected function setUp()
    {
        $this->markTestIncomplete('Waiting for interface update');

        $this->userManager  = $this->getMock('FOS\UserBundle\Model\UserManagerInterface');
        $this->userProvider = new UserProvider($this->userManager);
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoadUserByInvalidUsername()
    {
        $this->userManager
            ->expects($this->once())
            ->method('findUserByUsernameOrEmail')
            ->with('foobar')
            ->will($this->returnValue(null));

        $this->userProvider->loadUserByUsername('foobar');
    }

    public function testRefreshUserBy()
    {
        $user = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
                    ->setMethods(array('getId'))
                    ->getMock();

        $user->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('123'));

        $refreshedUser = $this->getMock('FOS\UserBundle\Model\UserInterface');

        $this->userManager->expects($this->once())
            ->method('findUserBy')
            ->with(array('id' => '123'))
            ->will($this->returnValue($refreshedUser));

        $this->assertSame($refreshedUser, $this->userProvider->refreshUser($user));
    }

    /**
     * @expectedException Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testRefreshInvalidUser()
    {
        $user = $this->getMock('Symfony\Component\Security\Core\User\UserInterface');

        $this->userProvider->refreshUser($user);
    }
}
