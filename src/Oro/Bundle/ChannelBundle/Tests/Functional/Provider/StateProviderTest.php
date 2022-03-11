<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Provider;

use Oro\Bundle\ChannelBundle\Entity\CustomerIdentity;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;
use Oro\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadChannels;
use Oro\Bundle\ChannelBundle\Tests\Functional\Fixture\LoadRestrictedUser;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class StateProviderTest extends WebTestCase
{
    /** @var StateProvider */
    private $stateProvider;

    protected function setUp(): void
    {
        $this->initClient();
        $this->stateProvider = $this->getContainer()->get('oro_channel.provider.state_provider');
        $this->loadFixtures(
            [
                LoadOrganization::class,
                LoadChannels::class,
                LoadRestrictedUser::class
            ]
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->getContainer()->get('oro_channel.state_cache')->clear();
    }

    public function testIsEntityEnabledForNonSupportedEntity()
    {
        $this->getContainer()->get('oro_channel.state_cache')->clear();
        $this->assertFalse($this->stateProvider->isEntityEnabled(\stdClass::class));
    }

    public function testIsEntityEnabledWithoutLoggedUser()
    {
        $this->getContainer()->get('oro_channel.state_cache')->clear();
        $this->assertTrue($this->stateProvider->isEntityEnabled(CustomerIdentity::class));
    }

    public function testIsEntityEnabledForAdminUser()
    {
        $organization = $this->getReference('organization');

        $this->stateProvider->clearOrganizationCache($organization->getId());
        $adminToken = new UsernamePasswordOrganizationToken(
            'admin',
            'admin',
            'key',
            $organization
        );
        $this->getContainer()->get('security.token_storage')->setToken($adminToken);
        $this->assertTrue($this->stateProvider->isEntityEnabled(CustomerIdentity::class));
    }

    public function testIsEntityEnabledForRestrictedUser()
    {
        $organization = $this->getReference('organization');
        $restrictedUser = $this->getReference('restrictedUser');

        $this->stateProvider->clearOrganizationCache($organization->getId());
        $restrictedUserToken = new UsernamePasswordOrganizationToken(
            $restrictedUser->getUserName(),
            'test',
            'key',
            $organization
        );
        $this->getContainer()->get('security.token_storage')->setToken($restrictedUserToken);
        $this->assertTrue($this->stateProvider->isEntityEnabled(CustomerIdentity::class));
    }

    public function testIsEntitiesEnabledInSomeChannel()
    {
        $container = $this->getContainer();
        $organization = $this->getReference('organization');

        $adminToken = new UsernamePasswordOrganizationToken(
            'admin',
            'admin',
            'key',
            $organization
        );
        $container->get('security.token_storage')->setToken($adminToken);
        $this->assertTrue($this->stateProvider->isEntitiesEnabledInSomeChannel([CustomerIdentity::class]));
    }
}
