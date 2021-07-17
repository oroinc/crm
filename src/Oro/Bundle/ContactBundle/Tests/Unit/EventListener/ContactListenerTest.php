<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\EventListener\ContactListener;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ContactListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContactListener */
    protected $contactListener;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $tokenStorage;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->contactListener = new ContactListener($this->tokenStorage);
    }

    protected function tearDown(): void
    {
        unset($this->tokenStorage);
        unset($this->contactListener);
    }

    /**
     * @param Contact $entity
     * @param bool $mockToken
     * @param bool $mockUser
     * @dataProvider prePersistAndPreUpdateDataProvider
     */
    public function testPrePersist($entity, $mockToken = false, $mockUser = false)
    {
        $user = $mockUser ? new User() : null;
        $this->mockSecurityContext($mockToken, $mockUser, $user);

        $em = $this->getEntityManagerMock();

        if ($mockUser) {
            $uow = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')->disableOriginalConstructor()->getMock();

            $em->expects($this->any())->method('getUnitOfWork')
                ->will($this->returnValue($uow));
        }

        $args = new LifecycleEventArgs($entity, $em);

        $this->contactListener->prePersist($entity, $args);

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

    public function testPrePersistWithAlreadySetCreatedAtAndCreatedBy()
    {
        $entity = new Contact();
        $createdAt = new \DateTime();
        $createdBy = new User();
        $entity
            ->setCreatedAt($createdAt)
            ->setCreatedBy($createdBy);

        $this->mockSecurityContext();

        $em = $this->getEntityManagerMock();

        $args = new LifecycleEventArgs($entity, $em);

        $this->contactListener->prePersist($entity, $args);

        $this->assertSame($createdAt, $entity->getCreatedAt());
        $this->assertSame($createdBy, $entity->getCreatedBy());
    }

    /**
     * @param Contact $entity
     * @param bool $mockToken
     * @param bool $mockUser
     * @param bool $detachedUser
     * @param bool $reloadUser
     * @dataProvider prePersistAndPreUpdateDataProvider
     */
    public function testPreUpdate(
        $entity,
        $mockToken = false,
        $mockUser = false,
        $detachedUser = null,
        $reloadUser = null
    ) {
        $oldDate = new \DateTime('2012-12-12 12:12:12');
        $oldUser = new User();
        $oldUser->setFirstName('oldUser');
        $entity->setUpdatedAt($oldDate);
        $entity->setUpdatedBy($oldUser);

        $initialEntity = clone $entity;

        $newUser = null;
        if ($mockUser) {
            $newUser = new User();
            $newUser->setFirstName('newUser');
        }

        $this->mockSecurityContext($mockToken, $mockUser, $newUser);

        $unitOfWork = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->setMethods(array('propertyChanged', 'getEntityState'))
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager = $this->getEntityManagerMock($reloadUser, $newUser);
        $entityManager->expects($this->any())
            ->method('getUnitOfWork')
            ->will($this->returnValue($unitOfWork));

        $callIndex = 0;
        if (null !== $detachedUser) {
            $unitOfWork->expects($this->at($callIndex++))
                ->method('getEntityState')
                ->with($newUser)
                ->will($this->returnValue($detachedUser ? UnitOfWork::STATE_DETACHED : UnitOfWork::STATE_MANAGED));
        }
        $unitOfWork->expects($this->at($callIndex++))
            ->method('propertyChanged')
            ->with($entity, 'updatedAt', $oldDate, $this->isInstanceOf('\DateTime'));
        $unitOfWork->expects($this->at($callIndex))
            ->method('propertyChanged')
            ->with($entity, 'updatedBy', $oldUser, $newUser);

        $changeSet = array();
        $args = new PreUpdateEventArgs($entity, $entityManager, $changeSet);

        $this->contactListener->preUpdate($entity, $args);

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
                'detachedUser' => false,
                'reloadUser' => false,
            ),
            'with a detached' => array(
                'entity' => new Contact(),
                'mockToken' => true,
                'mockUser' => true,
                'detachedUser' => true,
                'reloadUser' => true,
            ),
        );
    }

    public function testPreUpdateWhenNoUser()
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())
            ->method('getUser')
            ->willReturn(new \stdClass());
        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        /** @var EntityManager|\PHPUnit\Framework\MockObject\MockObject $entityManager */
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('getUnitOfWork')
            ->willReturn($this->createMock(UnitOfWork::class));

        $changeSet = [];
        $firstUpdatedAt = new \DateTime('-1 day');
        $firstUpdatedBy = new User();
        $contact = new Contact();
        $contact->setUpdatedAt($firstUpdatedAt);
        $contact->setUpdatedBy($firstUpdatedBy);

        $args = new PreUpdateEventArgs($contact, $entityManager, $changeSet);
        $this->contactListener->preUpdate($contact, $args);
        $this->assertGreaterThanOrEqual($firstUpdatedAt, $contact->getUpdatedAt());
        $this->assertNull($contact->getUpdatedBy());
    }

    /**
     * @param bool $reloadUser
     * @param object $newUser
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getEntityManagerMock($reloadUser = false, $newUser = null)
    {
        $result = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->setMethods(array('getUnitOfWork', 'find'))
            ->disableOriginalConstructor()
            ->getMock();

        if ($reloadUser) {
            $result->expects($this->once())->method('find')
                ->with('OroUserBundle:User')
                ->will($this->returnValue($newUser));
        } else {
            $result->expects($this->never())->method('find');
        }

        return $result;
    }

    /**
     * @param bool $mockToken
     * @param bool $mockUser
     * @param User|null $user
     */
    protected function mockSecurityContext($mockToken = false, $mockUser = false, $user = null)
    {
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

            $this->tokenStorage->expects($this->any())
                ->method('getToken')
                ->will($this->returnValue($token));
        }
    }
}
