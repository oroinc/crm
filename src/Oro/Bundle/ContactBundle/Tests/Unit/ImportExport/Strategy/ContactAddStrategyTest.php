<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\ImportExport\Strategy;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\ContactBundle\Entity\Method;
use Oro\Bundle\ContactBundle\Entity\Source;
use Oro\Bundle\ContactBundle\ImportExport\Strategy\ContactAddStrategy;
use Oro\Bundle\ContactBundle\ImportExport\Strategy\ContactImportHelper;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Event\StrategyEvent;
use Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException;
use Oro\Bundle\ImportExportBundle\Exception\LogicException;
use Oro\Bundle\ImportExportBundle\Field\DatabaseHelper;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class ContactAddStrategyTest extends TestCase
{
    private ContactAddStrategy $addStrategy;
    private EventDispatcherInterface $eventDispatcher;
    private MockObject&ImportStrategyHelper $strategyHelper;
    private MockObject&FieldHelper $fieldHelper;
    private MockObject&DatabaseHelper $databaseHelper;
    private MockObject&ContextInterface $context;
    private MockObject&TokenStorageInterface $tokenStorage;
    private MockObject&ContactImportHelper $contactImportHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->strategyHelper = $this->createMock(ImportStrategyHelper::class);
        $this->fieldHelper = $this->createMock(FieldHelper::class);
        $this->databaseHelper = $this->createMock(DatabaseHelper::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->contactImportHelper = $this->createMock(ContactImportHelper::class);

        $this->addStrategy = new ContactAddStrategy(
            $this->eventDispatcher,
            $this->strategyHelper,
            $this->fieldHelper,
            $this->databaseHelper
        );
    }

    public function testThatContextIsSet(): void
    {
        $this->expectExceptionObject(
            new LogicException('Strategy must have import/export context')
        );

        $this->addStrategy->process(null);
    }

    public function testThatEntityName(): void
    {
        $this->addStrategy->setImportExportContext($this->createMock(ContextInterface::class));

        $this->expectExceptionObject(
            new LogicException('Strategy must know about entity name')
        );

        $this->addStrategy->process(null);
    }

    public function testThatCorrectEntityIsPassed(): void
    {
        $this->addStrategy->setImportExportContext($this->createMock(ContextInterface::class));
        $this->addStrategy->setEntityName('Contact');
        $this->expectExceptionObject(
            new InvalidArgumentException('Imported entity must be instance of Contact')
        );

        $this->addStrategy->process(null);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testThatEntityProcessSingleRelations(): void
    {
        $this->makeValidAddStrategy();

        $entity = $this->createMock(Contact::class);

        $this->checkEventDispatcher($entity);

        $entity->expects($this->once())
            ->method('getGroups')
            ->willReturn([]);
        $entity->expects($this->once())
            ->method('getAccounts')
            ->willReturn([]);
        $entity->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);

        $this->databaseHelper->expects($this->once())
            ->method('resetIdentifier')
            ->with($this->equalTo($entity));

        $relations = $this->prepareExistingEntitySingleRelations();

        //source
        $entity->expects($this->once())
            ->method('getSource')
            ->willReturn($this->createMock(Source::class));
        $entity->expects($this->once())
            ->method('setSource')
            ->with($this->equalTo($relations['source']));

        //method
        $entity->expects($this->once())
            ->method('getMethod')
            ->willReturn($this->createMock(Method::class));
        $entity->expects($this->once())
            ->method('setMethod')
            ->with($this->equalTo($relations['method']));

        //assignedTo
        $entity->expects($this->once())
            ->method('getAssignedTo')
            ->willReturn($this->createMock(User::class));
        $entity->expects($this->once())
            ->method('setAssignedTo')
            ->with($this->equalTo($relations['user']));

        //createdBy
        $entity->expects($this->once())
            ->method('getCreatedBy')
            ->willReturn($this->createMock(User::class));
        $entity->expects($this->once())
            ->method('setCreatedBy')
            ->with($this->equalTo($relations['createdBy']));

        //updatedBy
        $entity->expects($this->once())
            ->method('getUpdatedBy')
            ->willReturn($this->createMock(User::class));
        $entity->expects($this->once())
            ->method('setUpdatedBy')
            ->with($this->equalTo($relations['updatedBy']));

        $entity->expects($this->once())
            ->method('setReportsTo')
            ->with($this->equalTo(null));

        $this->contactImportHelper->expects($this->once())
            ->method('updateScalars')
            ->with($entity);

        $this->contactImportHelper->expects($this->once())
            ->method('updatePrimaryEntities')
            ->with($entity);

        $entity->expects($this->once())
            ->method('setOwner')
            ->with($this->equalTo(null));

        $entity->expects($this->once())
            ->method('setOrganization')
            ->with($this->equalTo(null));

        $this->addStrategy->process($entity);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function testThatEntityProcessMultipleRelations(): void
    {
        $this->makeValidAddStrategy();

        $entity = $this->createMock(Contact::class);

        $this->checkEventDispatcher($entity);

        $this->databaseHelper->expects($this->once())
            ->method('resetIdentifier')
            ->with($this->equalTo($entity));

        $relations = $this->prepareExistingEntityMultipleRelations();

        //groups
        $group = $this->createMock(Group::class);
        $entity->expects($this->once())
            ->method('getGroups')
            ->willReturn([$group]);
        $entity->expects($this->once())
            ->method('removeGroup')
            ->with($this->equalTo($group));
        $entity->expects($this->once())
            ->method('addGroup')
            ->with($this->equalTo($relations['group']));

        //accounts
        $account = $this->createMock(Account::class);
        $entity->expects($this->once())
            ->method('getAccounts')
            ->willReturn([$account]);
        $entity->expects($this->once())
            ->method('removeAccount')
            ->with($this->equalTo($account));
        $entity->expects($this->once())
            ->method('addAccount')
            ->with($this->equalTo($relations['account']));

        //addresses
        $address = $this->createMock(ContactAddress::class);
        $country = $this->createMock(Country::class);
        $region = $this->createMock(Region::class);
        $type = $this->createMock(AddressType::class);

        $entity->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);

        $address->expects($this->once())
            ->method('getCountry')
            ->willReturn($country);
        $address->expects($this->once())
            ->method('setCountry')
            ->with($this->equalTo($relations['country']));

        $address->expects($this->once())
            ->method('getRegion')
            ->willReturn($region);
        $address->expects($this->once())
            ->method('setRegion')
            ->with($this->equalTo($relations['region']));

        $address->expects($this->once())
            ->method('getTypes')
            ->willReturn([$type]);
        $address->expects($this->once())
            ->method('removeType')
            ->with($this->equalTo($type));
        $address->expects($this->once())
            ->method('addType')
            ->with($this->equalTo($relations['type']));

        $entity->expects($this->once())
            ->method('setReportsTo')
            ->with($this->equalTo(null));

        $this->contactImportHelper->expects($this->once())
            ->method('updateScalars')
            ->with($entity);

        $this->contactImportHelper->expects($this->once())
            ->method('updatePrimaryEntities')
            ->with($entity);

        $entity->expects($this->once())
            ->method('setOwner')
            ->with($this->equalTo(null));

        $entity->expects($this->once())
            ->method('setOrganization')
            ->with($this->equalTo(null));

        $this->addStrategy->process($entity);
    }

    public function testThatEntityProcessWithNoRelations(): void
    {
        $this->makeValidAddStrategy();

        $entity = $this->createMock(Contact::class);

        $entity->expects($this->once())
            ->method('getGroups')
            ->willReturn([]);
        $entity->expects($this->once())
            ->method('getAccounts')
            ->willReturn([]);
        $entity->expects($this->once())
            ->method('getAddresses')
            ->willReturn([]);

        $this->checkEventDispatcher($entity);

        $this->databaseHelper->expects($this->once())
            ->method('resetIdentifier')
            ->with($this->equalTo($entity));

        $entity->expects($this->once())
            ->method('setReportsTo')
            ->with($this->equalTo(null));

        $this->contactImportHelper->expects($this->once())
            ->method('updateScalars')
            ->with($entity);

        $this->contactImportHelper->expects($this->once())
            ->method('updatePrimaryEntities')
            ->with($entity);

        $entity->expects($this->once())
            ->method('setOwner')
            ->with($this->equalTo(null));

        $entity->expects($this->once())
            ->method('setOrganization')
            ->with($this->equalTo(null));

        $this->addStrategy->process($entity);
    }

    private function makeValidAddStrategy(): void
    {
        $this->addStrategy->setImportExportContext($this->context);
        $this->addStrategy->setEntityName(Contact::class);
        $this->addStrategy->setTokenStorage($this->tokenStorage);
        $this->addStrategy->setContactImportHelper($this->contactImportHelper);
    }

    private function prepareExistingEntitySingleRelations(): array
    {
        return $this->processExistingObjects([
            'source' => $this->createMock(Source::class),
            'method' => $this->createMock(Method::class),
            'user' => $this->createMock(User::class),
            'createdBy' => $this->createMock(User::class),
            'updatedBy' => $this->createMock(User::class)
        ]);
    }

    private function prepareExistingEntityMultipleRelations(): array
    {
        return $this->processExistingObjects([
            'group' => $this->createMock(Group::class),
            'account' => $this->createMock(Account::class),
            'country' => $this->createMock(Country::class),
            'region' => $this->createMock(Region::class),
            'type' => $this->createMock(AddressType::class),
        ]);
    }

    private function processExistingObjects(array $objects): array
    {
        $this->databaseHelper->expects($this->exactly(count($objects)))
            ->method('getIdentifier')
            ->willReturn(true);

        $consecutive = [];
        foreach ($objects as $object) {
            $consecutive[] = [$this->equalTo(get_class($object)), $this->equalTo(true)];
        }

        $this->databaseHelper->expects($this->exactly(count($objects)))
            ->method('find')
            ->withConsecutive(...$consecutive)
            ->willReturnOnConsecutiveCalls(...$objects);

        return $objects;
    }

    private function checkEventDispatcher(Contact $entity): void
    {
        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$this->callback(function (StrategyEvent $event) use ($entity) {
                    $this->assertEquals($entity, $event->getEntity());
                    $this->assertEquals($this->context, $event->getContext());
                    $this->assertEquals($this->addStrategy, $event->getStrategy());

                    return true;
                }),
                    $this->equalTo(StrategyEvent::PROCESS_BEFORE)
                ],
                [
                    $this->callback(function (StrategyEvent $event) use ($entity) {
                        $this->assertEquals($entity, $event->getEntity());
                        $this->assertEquals($this->context, $event->getContext());
                        $this->assertEquals($this->addStrategy, $event->getStrategy());

                        return true;
                    }),
                    $this->equalTo(StrategyEvent::PROCESS_AFTER)
                ]
            )
            ->willReturn($entity);
    }
}
