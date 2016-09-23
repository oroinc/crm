<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Functional\Controller\Api\Rest;

use FOS\RestBundle\Util\Codes;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\MarketingListBundle\Entity\MarketingList;
use Oro\Bundle\MarketingListBundle\Entity\MarketingListType;

/**
 * @dbIsolation
 */
class MarketingListControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());
    }

    public function testDelete()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $type = $em
            ->getRepository('OroMarketingListBundle:MarketingListType')
            ->find(MarketingListType::TYPE_DYNAMIC);

        $entity = new MarketingList();
        $entity
            ->setType($type)
            ->setName('list_name')
            ->setEntity('entity');

        $em->persist($entity);
        $em->flush($entity);

        $this->assertNotNull($entity->getId());

        $this->client->request(
            'DELETE',
            $this->getUrl('oro_api_delete_marketinglist', ['id' => $entity->getId()]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, Codes::HTTP_NO_CONTENT);
    }
}
