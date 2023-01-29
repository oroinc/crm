<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\EventListener\ContactListener;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ContactListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var TokenStorageInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $tokenStorage;

    /** @var ContactListener */
    private $contactListener;

    protected function setUp(): void
    {
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->contactListener = new ContactListener($this->tokenStorage);
    }

    /**
     * @dataProvider prePersistAndPreUpdateDataProvider
     */
    public function testPrePersist(Contact $entity, bool $mockToken = false, bool $mockUser = false)
    {
        $user = $mockUser ? new User() : null;
        $this->mockSecurityContext($mockToken, $mockUser, $user);

        $em = $this->createMock(EntityManager::class);
        $em->expects($this->never())
            ->method('find');

        if ($mockUser) {
            $uow = $this->createMock(UnitOfWork::class);

            $em->expects($this->any())
                ->method('getUnitOfWork')
                ->willReturn($uow);
        }

        $args = new LifecycleEventArgs($entity, $em);

        $this->contactListener->prePersist($entity, $args);

        $this->assertInstanceOf(\DateTime::class, $entity->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $entity->getUpdatedAt());
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

        $em = $this->createMock(EntityManager::class);
        $em->expects($this->never())
            ->method('find');

        $args = new LifecycleEventArgs($entity, $em);

        $this->contactListener->prePersist($entity, $args);

        $this->assertSame($createdAt, $entity->getCreatedAt());
        $this->assertSame($createdBy, $entity->getCreatedBy());
    }

    /**
     * @dataProvider prePersistAndPreUpdateDataProvider
     */
    public function testPreUpdate(
        Contact $entity,
        bool $mockToken = false,
        bool $mockUser = false,
        bool $detachedUser = null,
        bool $reloadUser = null
    ) {
        $oldDate = new \DateTime('2012-12-12 12:12:12');
        $oldUser = new User();
        $oldUser->setFirstName('oldUser');
        $entity->setUpdatedAt($oldDate);
        $entity->setUpdatedBy($oldUser);

        $newUser = null;
        if ($mockUser) {
            $newUser = new User();
            $newUser->setFirstName('newUser');
        }

        $this->mockSecurityContext($mockToken, $mockUser, $newUser);

        $unitOfWork = $this->createMock(UnitOfWork::class);

        $em = $this->createMock(EntityManager::class);
        if ($reloadUser) {
            $em->expects($this->once())
                ->method('find')
                ->with('OroUserBundle:User')
                ->willReturn($newUser);
        } else {
            $em->expects($this->never())
                ->method('find');
        }
        $em->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($unitOfWork);

        if (null !== $detachedUser) {
            $unitOfWork->expects($this->once())
                ->method('getEntityState')
                ->with($newUser)
                ->willReturn($detachedUser ? UnitOfWork::STATE_DETACHED : UnitOfWork::STATE_MANAGED);
        }
        $unitOfWork->expects($this->exactly(2))
            ->method('propertyChanged')
            ->withConsecutive(
                [$entity, 'updatedAt', $oldDate, $this->isInstanceOf(\DateTime::class)],
                [$entity, 'updatedBy', $oldUser, $newUser]
            );

        $changeSet = [];
        $args = new PreUpdateEventArgs($entity, $em, $changeSet);

        $this->contactListener->preUpdate($entity, $args);

        $this->assertInstanceOf(\DateTime::class, $entity->getUpdatedAt());
        if ($mockToken && $mockUser) {
            $this->assertEquals($newUser, $entity->getUpdatedBy());
        } else {
            $this->assertNull($entity->getUpdatedBy());
        }
    }

    public function prePersistAndPreUpdateDataProvider(): array
    {
        return [
            'no token' => [
                'entity'    => new Contact(),
                'mockToken' => false,
                'mockUser'  => false,
            ],
            'no user' => [
                'entity'    => new Contact(),
                'mockToken' => true,
                'mockUser'  => false,
            ],
            'with a user' => [
                'entity'    => new Contact(),
                'mockToken' => true,
                'mockUser'  => true,
                'detachedUser' => false,
                'reloadUser' => false,
            ],
            'with a detached' => [
                'entity' => new Contact(),
                'mockToken' => true,
                'mockUser' => true,
                'detachedUser' => true,
                'reloadUser' => true,
            ],
        ];
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

    private function mockSecurityContext(bool $mockToken = false, bool  $mockUser = false, User $user = null): void
    {
        if ($mockToken) {
            $token = $this->createMock(TokenInterface::class);

            if ($mockUser) {
                $token->expects($this->any())
                    ->method('getUser')
                    ->willReturn($user);
            }

            $this->tokenStorage->expects($this->any())
                ->method('getToken')
                ->willReturn($token);
        }
    }
}
