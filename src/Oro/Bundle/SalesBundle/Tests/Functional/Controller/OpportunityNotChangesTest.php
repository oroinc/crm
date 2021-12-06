<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadFullOpportunityFixtures;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class OpportunityNotChangesTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient(
            ['debug' => false],
            $this->generateBasicAuthHeader()
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures([
            LoadFullOpportunityFixtures::class
        ]);
    }

    public function testSubmitOpportunityForm()
    {
        /** @var Opportunity $opportunity */
        $opportunity = $this->getReference('full_opportunity');
        $originUpdatedAt = $opportunity->getUpdatedAt();

        sleep(1);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_sales_opportunity_update', ['id' => $opportunity->getId()])
        );

        $form = $crawler->selectButton('Save and Close')->form();

        $this->client->followRedirects(true);
        $this->client->submit($form);

        /** @var ManagerRegistry $manager */
        $registry = $this->client->getContainer()->get('doctrine');
        /** @var EntityManagerInterface $manager */
        $manager = $registry->getManagerForClass(Opportunity::class);

        $newOpportunity = $manager->find(Opportunity::class, $opportunity->getId());

        $this->assertEquals(
            $originUpdatedAt->format('Y-m-d H:i:s'),
            $newOpportunity->getUpdatedAt()->format('Y-m-d H:i:s'),
            'Entity shouldn\'t change when do submit form without new data!'
        );
    }
}
