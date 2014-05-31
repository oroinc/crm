<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\EventListener;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class ChannelOwnerSetListenerTest extends WebTestCase
{
    const FIXTURE_NS = 'OroCRM\\Bundle\\MagentoBundle\\Tests\\Functional\\Fixture\\';

    public function setUp()
    {
        $this->initClient(
            array(),
            array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1])
        );

        $fixtures = [
            self::FIXTURE_NS . 'LoadMagentoChannel',
            self::FIXTURE_NS . 'LoadCustomerContact',
            self::FIXTURE_NS . 'LoadNotAssociatedEntities'
        ];
        $this->loadFixtures($fixtures);
    }

    protected function postFixtureLoad()
    {
        $adminUser = $this->getContainer()->get('oro_user.manager')->loadUserByUsername('admin');
        $this->getReferenceRepository()->setReference('admin_user', $adminUser);
    }

    public function testDefaultOwnerSet()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('channel');
        /** @var Customer $customer */
        $customer = $this->getReference('customer');

        if (!($channel && $customer)) {
            $this->markTestIncomplete('Invalid fixtures, unable to perform test case');
        }

        $this->assertNull($channel->getDefaultUserOwner(), 'Should not have owner at start');


    }
}
