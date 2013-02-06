<?php

namespace Oro\Bundle\UserBundle\Tests\Entity;

use Symfony\Component\DependencyInjection\Container;

use Oro\Bundle\UserBundle\Entity\UserManager;

class UserManagerTest extends \PHPUnit_Framework_TestCase
{
    const USER_CLASS = 'Oro\Bundle\UserBundle\Entity\User';

    /**
     * @var Container
     */
    protected $container;

    protected $userManager;
    protected $om;
    protected $repository;

    protected function setUp()
    {
        $this->markTestSkipped('Waiting for interface update');

        if (!interface_exists('Doctrine\Common\Persistence\ObjectManager')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $this->container = new Container();

        $this->container->setParameter('oro_flexibleentity.flexible_config', array(
            'entities_config' => array(
                static::USER_CLASS => array(
                    'flexible_manager'     => 'oro_user.manager',
                    'flexible_class'       => 'Oro\Bundle\UserBundle\Entity\User',
                    'flexible_value_class' => 'Oro\Bundle\UserBundle\Entity\UserValue',
                    'default_locale'       => 'en_US',
                )
            )
        ));

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

        $this->userManager = $this->createUserManager($this->container, static::USER_CLASS, $this->om, $ef);
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

    protected function createUserManager($container, $userClass, $objectManager, $encoderFactory)
    {
        return new UserManager($container, $userClass, $objectManager, $encoderFactory);
    }
}
