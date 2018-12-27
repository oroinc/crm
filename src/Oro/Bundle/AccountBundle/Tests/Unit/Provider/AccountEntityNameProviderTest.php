<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\Provider;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\Provider\AccountEntityNameProvider;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;

class AccountEntityNameProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var AccountEntityNameProvider */
    private $provider;

    protected function setUp()
    {
        $this->provider = new AccountEntityNameProvider();
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
        $this->assertFalse(
            $this->provider->getNameDQL(EntityNameProviderInterface::FULL, null, Account::class, 'account')
        );
    }
}
