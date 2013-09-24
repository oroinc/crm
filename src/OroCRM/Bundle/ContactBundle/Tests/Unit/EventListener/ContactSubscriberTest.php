<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use OroCRM\Bundle\ContactBundle\EventListener\ContactSubscriber;
use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

class ContactSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactSubscriber
     */
    protected $contactSubscriber;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    protected function setUp()
    {
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->setMethods(array('get'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->contactSubscriber = new ContactSubscriber($this->container);
    }

    protected function tearDown()
    {
        unset($this->container);
        unset($this->contactSubscriber);
    }

    /**
     * @param bool $mockToken
     * @param bool $mockUser
     * @param User|null $user
     */
    protected function mockSecurityContext($mockToken = false, $mockUser = false, $user = null)
    {
        $securityContext = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')
            ->setMethods(array('getToken'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        if ($mockToken) {
            $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
                ->setMethods(array('getUser'))
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();

            if ($mockUser) {
                $token->expects($this->any())
                    ->method('getUser')
                    ->will($this->returnValue($user));
            }

            $securityContext->expects($this->any())
                ->method('getToken')
                ->will($this->returnValue($token));
        }

        $this->container->expects($this->any())
            ->method('get')
            ->with('security.context')
            ->will($this->returnValue($securityContext));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getEntityManagerMock()
    {
        return $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->setMethods(array('getUnitOfWork'))
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param object $entity
     * @param bool $mockToken
     * @param bool $mockUser
     * @dataProvider prePersistAndPreUpdateDataProvider
     */
    public function testPrePersist($entity, $mockToken = false, $mockUser = false)
    {
        $this->markTestIncomplete('CRM-527: Update unit tests');
        $initialEntity = clone $entity;

        $user = $mockUser ? new User() : null;
        $this->mockSecurityContext($mockToken, $mockUser, $user);

        $em = $this->getEntityManagerMock();

        if ($mockUser) {
            $uow = $this->getMock('Doctrine\ORM\EntityManager');

            $em->expects($this->exactly(2))->method('getUnitOfWork')
                ->will($this->returnValue($uow));
        }

        $args = new LifecycleEventArgs($entity, $em);

        $this->contactSubscriber->prePersist($args);

        if (!$entity instanceof Contact) {
            $this->assertEquals($initialEntity, $entity);
            return;
        }

        $this->assertInstanceOf('\DateTime', $entity->getCreatedAt());
        $this->assertInstanceOf('\DateTime', $entity->getUpdatedAt());
        if ($mockToken && $mockUser) {
            $this->assertEquals($user, $entity->getCreatedBy());
            $this->assertEquals($user, $entity->getUpdatedBy());
        } else {
            $this->assertNull($entity->getCreatedBy());
            $this->assertNull($entity->getUpdatedBy());
        }
    }

    /**
     * @param object $entity
     * @param bool $mockToken
     * @param bool $mockUser
     * @dataProvider prePersistAndPreUpdateDataProvider
     */
    public function testPreUpdate($entity, $mockToken = false, $mockUser = false)
    {
        $this->markTestIncomplete('CRM-527: Update unit tests');
        $oldDate = new \DateTime('2012-12-12 12:12:12');
        $oldUser = new User();
        $oldUser->setFirstname('oldUser');
        if ($entity instanceof Contact) {
            $entity->setUpdatedAt($oldDate);
            $entity->setUpdatedBy($oldUser);
        }

        $initialEntity = clone $entity;

        $newUser = null;
        if ($mockUser) {
            $newUser = new User();
            $newUser->setFirstname('newUser');
        }

        $this->mockSecurityContext($mockToken, $mockUser, $newUser);

        $unitOfWork = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->setMethods(array('propertyChanged'))
            ->disableOriginalConstructor()
            ->getMock();
        if ($entity instanceof Contact) {
            $unitOfWork->expects($this->at(0))
                ->method('propertyChanged')
                ->with($entity, 'updatedAt', $oldDate, $this->isInstanceOf('\DateTime'));
            $unitOfWork->expects($this->at(1))
                ->method('propertyChanged')
                ->with($entity, 'updatedBy', $oldUser, $newUser);
        } else {
            $unitOfWork->expects($this->never())
                ->method('propertyChanged');
        }
        $entityManager = $this->getEntityManagerMock();
        $entityManager->expects($this->any())
            ->method('getUnitOfWork')
            ->will($this->returnValue($unitOfWork));

        $changeSet = array();
        $args = new PreUpdateEventArgs($entity, $entityManager, $changeSet);

        $this->contactSubscriber->preUpdate($args);

        if (!$entity instanceof Contact) {
            $this->assertEquals($initialEntity, $entity);
            return;
        }

        $this->assertInstanceOf('\DateTime', $entity->getUpdatedAt());
        if ($mockToken && $mockUser) {
            $this->assertEquals($newUser, $entity->getUpdatedBy());
        } else {
            $this->assertNull($entity->getUpdatedBy());
        }
    }

    /**
     * @return array
     */
    public function prePersistAndPreUpdateDataProvider()
    {
        return array(
            'not a contact' => array(
                'entity'    => new \DateTime('now'),
                'mockToken' => false,
                'mockUser'  => false,
            ),
            'no token' => array(
                'entity'    => new Contact(),
                'mockToken' => false,
                'mockUser'  => false,
            ),
            'no user' => array(
                'entity'    => new Contact(),
                'mockToken' => true,
                'mockUser'  => false,
            ),
            'with a user' => array(
                'entity'    => new Contact(),
                'mockToken' => true,
                'mockUser'  => true,
            ),
        );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array('prePersist', 'preUpdate'), $this->contactSubscriber->getSubscribedEvents());
    }
}
