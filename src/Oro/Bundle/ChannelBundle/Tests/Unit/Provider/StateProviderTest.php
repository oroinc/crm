<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Provider;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class StateProviderTest extends TestCase
{
    private AbstractAdapter&MockObject $cacheProvider;
    private SettingsProvider&MockObject $settingsProvider;
    private ManagerRegistry&MockObject $registry;
    private TokenAccessorInterface&MockObject $token;
    private StateProvider $stateProvider;

    #[\Override]
    protected function setUp(): void
    {
        $this->cacheProvider = $this->createMock(AbstractAdapter::class);
        $this->settingsProvider = $this->createMock(SettingsProvider::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->token = $this->createMock(TokenAccessorInterface::class);
        $this->stateProvider = new StateProvider(
            $this->settingsProvider,
            $this->cacheProvider,
            $this->registry,
            $this->token
        );
    }

    public function testEnabledEntitiesNotCached(): void
    {
        $entityManager = $this->createMock(EntityManager::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        foreach (['distinct','select','from','innerJoin'] as $method) {
            $queryBuilder->expects(self::once())
                ->method($method)
                ->willReturnSelf();
        }
        $entityManager->expects(self::once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
        $query = $this->createMock(AbstractQuery::class);
        $this->registry->expects(self::once())
            ->method('getManager')
            ->willReturn($entityManager);
        $queryBuilder->expects(self::once())
            ->method('getQuery')
            ->willReturn($query);
        $query->expects(self::once())
            ->method('getArrayResult')
            ->willReturn([]);

        $this->cacheProvider->expects(self::once())
            ->method('get')
            ->willReturnCallback(function ($cacheKey, $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });

        self::assertFalse($this->stateProvider->isEntityEnabled(User::class));
    }

    public function testEnabledEntitiesCached(): void
    {
        $this->cacheProvider->expects(self::once())
            ->method('get')
            ->willReturn([
                B2bCustomer::class => true,
                'Oro\Bundle\CustomerBundle\Entity\Customer' => true,
                'Oro\Bundle\UserBundle\Entity\User' => true
            ]);
        self::assertTrue($this->stateProvider->isEntityEnabled(User::class));
    }

    public function testProcessChannelChange(): void
    {
        $this->token->expects(self::once())
            ->method('getOrganizationId')
            ->willReturn(1);
        $this->cacheProvider->expects(self::once())
            ->method('delete')
            ->with('oro_channel_state_data_1');
        $this->stateProvider->processChannelChange();
    }

    public function testClearOrganizationCache(): void
    {
        $organizationId = 1;
        $this->cacheProvider->expects(self::once())
            ->method('delete')
            ->with('oro_channel_state_data_1');
        $this->stateProvider->clearOrganizationCache($organizationId);
    }
}
