<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Functional\Controller\Api\Rest;

use FOS\RestBundle\Util\Codes;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolation
 */
class MarketingListRemovedItemControllerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());

        $this->loadFixtures(
            [ __NAMESPACE__ . '\\DataFixtures\\LoadMarketingListData']
        );
    }

    public function testCreate()
    {
        $marketingListId = $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroCRMMarketingListBundle:MarketingList')
            ->findOneBy([])
            ->getId();

        $this->client->request(
            'POST',
            $this->getUrl('orocrm_api_post_marketinglist_removeditem'),
            [
                'entityId'      => 1,
                'marketingList' => $marketingListId
            ]
        );

        $marketingListRemovedItem = $this->getJsonResponseContent(
            $this->client->getResponse(),
            Codes::HTTP_CREATED
        );

        return $marketingListRemovedItem['id'];
    }

    /**
     * @depends testCreate
     *
     * @param integer $id
     */
    public function testDelete($id)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('orocrm_api_delete_marketinglist_removeditem', ['id' => $id])
        );

        $this->assertEmptyResponseStatusCodeEquals(
            $this->client->getResponse(),
            Codes::HTTP_NO_CONTENT
        );
    }
}
