<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\EventListener;

use Symfony\Component\DomCrawler\Form;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

/**
 * @outputBuffering enabled
 * @dbIsolation
 */
class ChannelOwnerSetListenerTest extends WebTestCase
{
    const FIXTURE_NS = 'OroCRM\\Bundle\\MagentoBundle\\Tests\\Functional\\Fixture\\';

    public function setUp()
    {
        $this->initClient([], array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1]));
        $fixtures = [
            self::FIXTURE_NS . 'LoadMagentoChannel',
            self::FIXTURE_NS . 'LoadCustomerContact',
            self::FIXTURE_NS . 'LoadNotAssociatedEntities',
            self::FIXTURE_NS . 'LoadOwnerUser',
        ];
        $this->loadFixtures($fixtures);
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

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_integration_channel_update', ['id' => $channel->getId()])
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $this->assertEmpty($form['oro_integration_channel_form[defaultUserOwner]']->getValue());

        $form['oro_integration_channel_form[defaultUserOwner]'] = $this->getReference('owner_user')->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains("Channel saved", $crawler->html());

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $em->refresh($customer);
        $em->refresh($channel);
        $em->refresh($this->getReference('not_associated_account'));
        $em->refresh($this->getReference('not_associated_contact'));
        $em->refresh($this->getReference('not_associated_entities_owner'));

        $this->assertSame(
            $this->getReference('not_associated_entities_owner'),
            $this->getReference('not_associated_account')->getOwner(),
            'Changes should not affect all account entities'
        );

        $this->assertSame(
            $this->getReference('not_associated_entities_owner'),
            $this->getReference('not_associated_contact')->getOwner(),
            'Changes should not affect all contact entities'
        );
    }
}
