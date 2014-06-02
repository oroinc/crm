<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Util\ClassUtils;

use Symfony\Component\DomCrawler\Form;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

/**
 * @dbIsolation
 */
class ChannelOwnerSetListenerTest extends WebTestCase
{
    const FIXTURE_NS = 'OroCRM\\Bundle\\MagentoBundle\\Tests\\Functional\\Fixture\\';

    public function setUp()
    {
        $this->initClient([], array_merge($this->generateBasicAuthHeader(), ['HTTP_X-CSRF-Header' => 1]), true);
        $fixtures = [
            self::FIXTURE_NS . 'LoadMagentoChannel',
            self::FIXTURE_NS . 'LoadCustomerContact',
            self::FIXTURE_NS . 'LoadNotAssociatedEntities',
            self::FIXTURE_NS . 'LoadOwnerUser',
        ];
        $this->loadFixtures($fixtures, true);
    }

    /**
     * @dataProvider scenarioProvider
     *
     * @param bool $emptyOwnerScenario
     */
    public function testDefaultOwnerSet($emptyOwnerScenario = false)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var Channel $channel */
        $channel = $this->getReference('channel');
        /** @var Customer $customer */
        $customer = $this->getReference('customer');

        if (!($channel && $customer)) {
            $this->markTestIncomplete('Invalid fixtures, unable to perform test case');
        }

        if ($emptyOwnerScenario) {
            $this->resetOwner($customer->getContact());
            $this->resetOwner($customer->getAccount());
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

        /** @var EntityManager $em */
        $notAssociatedAccount = $em->merge($this->getReference('not_associated_account'));
        $notAssociatedContact = $em->merge($this->getReference('not_associated_contact'));

        $this->assertSame(
            $this->getReference('not_associated_entities_owner')->getId(),
            $notAssociatedAccount->getOwner()->getId(),
            'Changes should not affect all account entities'
        );

        $this->assertSame(
            $this->getReference('not_associated_entities_owner')->getId(),
            $notAssociatedContact->getOwner()->getId(),
            'Changes should not affect all contact entities'
        );

        $newOwnerId = $this->getReference('owner_user')->getId();
        if ($emptyOwnerScenario) {
            $this->assertEquals($newOwnerId, $customer->getAccount()->getOwner()->getId());
            $this->assertEquals($newOwnerId, $customer->getContact()->getOwner()->getId());
        } else {
            $this->assertNotEquals($newOwnerId, $customer->getAccount()->getOwner()->getId());
            $this->assertNotEquals($newOwnerId, $customer->getContact()->getOwner()->getId());
        }
    }

    /**
     * @return array
     */
    public function scenarioProvider()
    {
        return [
            'owner is set, should not be modified' => [false],
            'owner is empty, should be changed'    => [true]
        ];
    }

    /**
     * @param Contact|Account $entity
     */
    protected function resetOwner($entity)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $qb = $em->createQueryBuilder();
        $qb->update(ClassUtils::getClass($entity), 'e')
            ->set('e.owner', 'NULL')
            ->where($qb->expr()->eq('e.id', $entity->getId()));

        $qb->getQuery()->execute();
    }
}
