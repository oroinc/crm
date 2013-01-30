<?php

namespace Oro\Bundle\UserBundle\Tests\Entity;

use Oro\Bundle\UserBundle\Entity\UserManager;

class UserManagerTest extends \PHPUnit_Framework_TestCase
{
    const USER_CLASS = 'Oro\Bundle\UserBundle\Entity\User';

    protected $userManager;
    protected $om;
    protected $repository;

    protected function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ObjectManager')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $c     = $this->getMock('FOS\UserBundle\Util\CanonicalizerInterface');
        $ef    = $this->getMock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
        $class = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $this->om         = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $this->om->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo(static::USER_CLASS))
            ->will($this->returnValue($this->repository));

        $this->om->expects($this->any())
            ->method('getClassMetadata')
            ->with($this->equalTo(static::USER_CLASS))
            ->will($this->returnValue($class));

        $class->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(static::USER_CLASS));

        $this->userManager = $this->createUserManager($ef, $c, $this->om, static::USER_CLASS);
    }

    public function testFindUserByUsername()
    {
        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(array('username' => 'jack')));

        $this->userManager->findUserByUsername('jack');
    }

    public function testFindUserByEmail()
    {
        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(array('email' => 'jack@email.org')));

        $this->userManager->findUserByEmail('jack@email.org');
    }

    protected function createUserManager($encoderFactory, $canonicalizer, $objectManager, $userClass)
    {
        return new UserManager($encoderFactory, $canonicalizer, $canonicalizer, $objectManager, $userClass);
    }
}
