<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\Provider;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\Provider\AccountEntityNameProvider;
use Oro\Bundle\EntityBundle\Provider\EntityNameProvider;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;

class AccountEntityNameProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var AccountEntityNameProvider */
    private $provider;

    /** @var EntityNameProvider */
    private $defaultEntityNameProvider;

    protected function setUp(): void
    {
        $this->defaultEntityNameProvider = $this->createMock(EntityNameProvider::class);

        $this->provider = new AccountEntityNameProvider($this->defaultEntityNameProvider);
    }

    public function testGetNameForShortFormat(): void
    {
        $this->assertFalse($this->provider->getName(EntityNameProviderInterface::SHORT, null, new Account()));
        $this->assertFalse($this->provider->getName(null, null, new Account()));
    }

    public function testGetNameForUnsupportedEntity(): void
    {
        $this->assertFalse(
            $this->provider->getName(EntityNameProviderInterface::FULL, null, new \stdClass())
        );
    }

    public function testGetName(): void
    {
        $account = new Account();
        $account->setName('default name');

        $this->assertEquals(
            'default name',
            $this->provider->getName(EntityNameProviderInterface::FULL, null, $account)
        );
    }

    public function testGetNameDQL(): void
    {
        $locale = null;
        $className = Account::class;
        $alias = 'account';

        $this->defaultEntityNameProvider->expects($this->exactly(2))
            ->method('getNameDQL')
            ->with(
                EntityNameProviderInterface::SHORT,
                $locale,
                $className,
                $alias
            )
            ->willReturn('Account Short Name DQL');

        $this->assertEquals(
            'Account Short Name DQL',
            $this->provider->getNameDQL(EntityNameProviderInterface::FULL, $locale, $className, $alias)
        );

        $this->assertEquals(
            'Account Short Name DQL',
            $this->provider->getNameDQL(EntityNameProviderInterface::SHORT, $locale, $className, $alias)
        );
    }

    public function testGetNameDQLForUnsupportedClass(): void
    {
        $this->assertFalse(
            $this->provider->getNameDQL(EntityNameProviderInterface::FULL, null, \stdClass::class, 'account')
        );
    }
}
