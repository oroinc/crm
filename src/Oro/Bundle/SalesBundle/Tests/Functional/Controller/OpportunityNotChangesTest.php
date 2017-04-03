<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Controller;

use Symfony\Component\DomCrawler\Form;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadFullOpportunityFixtures;

class OpportunityNotChangesTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(
            ['debug' => false],
            array_merge($this->generateBasicAuthHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures([
            LoadFullOpportunityFixtures::class
        ]);
    }

    public function testSubmitOpportunityForm()
    {
        $opportunity = $this->getReference('full_opportunity');
        /**
         * @var $originUpdatedAt \DateTime
         */
        $originUpdatedAt = $opportunity->getUpdatedAt();

        sleep(1);

        $crawler = $this->client->request(
            'GET',
            $this->getUrl('oro_sales_opportunity_update', ['id' => $opportunity->getId()])
        );

        /** @var Form $form */
        $form = $crawler->selectButton('Save and Close')->form();

        $this->client->followRedirects(true);
        $this->client->submit($form);

        /**
         * @var $manager Registry
         */
        $registry = $this->client->getContainer()->get('doctrine');
        $manager = $registry->getManagerForClass('OroSalesBundle:Opportunity');

        $newOpportunity = $manager->find('OroSalesBundle:Opportunity', $opportunity->getId());

        $this->assertEquals(
            $originUpdatedAt->format('Y-m-d H:i:s'),
            $newOpportunity->getUpdatedAt()->format('Y-m-d H:i:s'),
            'Entity shouldn\'t change when do submit form without new data!'
        );
    }
}
