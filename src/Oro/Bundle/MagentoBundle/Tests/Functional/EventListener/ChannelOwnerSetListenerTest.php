<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;

/**
 * @dbIsolationPerTest
 */
class ChannelOwnerSetListenerTest extends WebTestCase
{
    const FIXTURE_NS = 'Oro\\Bundle\\MagentoBundle\\Tests\\Functional\\Fixture\\';

    protected function setUp(): void
    {
        $this->markTestSkipped('Magento integration is disabled in CRM-9202');
        $this->initClient([], $this->generateBasicAuthHeader());
        $fixtures = [
            self::FIXTURE_NS . 'LoadMagentoChannel',
            self::FIXTURE_NS . 'LoadCustomerContact',
            self::FIXTURE_NS . 'LoadNotAssociatedEntities',
            self::FIXTURE_NS . 'LoadOwnerUser',
        ];
        $this->loadFixtures($fixtures);
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
        $channel = $this->getReference('integration');
        /** @var Customer $customer */
        $customer = $this->getReference('customer');
        /** @var Order $customer */
        $order = $this->getReference('order');
        /** @var Cart $customer */
        $cart = $this->getReference('cart');

        if (!($channel && $customer && $order && $cart)) {
            $this->markTestIncomplete('Invalid fixtures, unable to perform test case');
        }

        if ($emptyOwnerScenario) {
            $this->resetOwner($customer->getContact());
            $this->resetOwner($customer->getAccount());
            $this->resetOwner($customer);
            $this->resetOwner($cart);
            $this->resetOwner($order);
        }

        $this->assertNull($channel->getDefaultUserOwner(), 'Should not have owner at start');

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_integration_update', ['id' => $channel->getId()])
        );
        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();
        $this->assertEmpty($form['oro_integration_channel_form[defaultUserOwner]']->getValue());

        $form['oro_integration_channel_form[defaultUserOwner]'] = $this->getReference('owner_user')->getId();

        $this->client->followRedirects(true);
        $crawler = $this->client->submit($form);

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString("Integration saved", $crawler->html());

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
            $this->assertEquals($newOwnerId, $this->getEntityOwnerId($customer->getAccount()));
            $this->assertEquals($newOwnerId, $this->getEntityOwnerId($customer->getContact()));
            $this->assertEquals($newOwnerId, $this->getEntityOwnerId($customer));
            $this->assertEquals($newOwnerId, $this->getEntityOwnerId($order));
            $this->assertEquals($newOwnerId, $this->getEntityOwnerId($cart));
        } else {
            $this->assertNotEquals($newOwnerId, $this->getEntityOwnerId($customer->getAccount()));
            $this->assertNotEquals($newOwnerId, $this->getEntityOwnerId($customer->getContact()));
            $this->assertNotEquals($newOwnerId, $this->getEntityOwnerId($customer));
            $this->assertNotEquals($newOwnerId, $this->getEntityOwnerId($order));
            $this->assertNotEquals($newOwnerId, $this->getEntityOwnerId($cart));
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
     * @param object $entity
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

    /**
     * @param object $entity
     *
     * @return int
     */
    protected function getEntityOwnerId($entity)
    {
        /** @var EntityManager $em */
        $em     = $this->getContainer()->get('doctrine.orm.entity_manager');
        $entity = $em->getRepository(ClassUtils::getClass($entity))->findOneById($entity->getId());

        return $entity->getOwner()->getId();
    }
}
