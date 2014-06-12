<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\CaseBundle\Entity\CaseSource;
use OroCRM\Bundle\CaseBundle\Entity\CaseStatus;

/**
 * @outputBuffering enabled
 * @dbIsolation
 * @dbReindex
 */
class CaseControllerTest extends WebTestCase
{
    /**
     * @var array
     */
    protected $case = [
        'subject'     => 'New case',
        'description' => 'New description',
        'owner'       => 1,
        'source'      => CaseSource::SOURCE_EMAIL,
        'status'      => CaseStatus::STATUS_OPEN
    ];

    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());
    }

    public function testCreate()
    {
        $request = [
            'case' => $this->case
        ];

        $this->client->request(
            'POST',
            $this->getUrl('orocrm_api_post_case'),
            $request
        );

        $case = $this->getJsonResponseContent($this->client->getResponse(), 201);

        return $case['id'];
    }

    /**
     * @depends testCreate
     */
    public function testCget()
    {
        $this->client->request(
            'GET',
            $this->getUrl('orocrm_api_get_cases'),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $cases = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCount(1, $cases);
    }

    /**
     * @depends testCreate
     * @param integer $id
     */
    public function testGet($id)
    {
        $this->client->request(
            'GET',
            $this->getUrl('orocrm_api_get_case', ['id' => $id]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $case = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals($this->case['subject'], $case['subject']);
    }

    /**
     * @depends testCreate
     * @param integer $id
     */
    public function testPut($id)
    {
        $updatedCase = array_merge($this->case, ['subject' => 'Updated subject']);
        $this->client->request(
            'PUT',
            $this->getUrl('orocrm_api_put_case', ['id' => $id]),
            ['case' => $updatedCase],
            [],
            $this->generateWsseAuthHeader()
        );
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('orocrm_api_get_case', ['id' => $id])
        );

        $case = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertEquals(
            'Updated subject',
            $case['subject']
        );

        $this->assertEquals($updatedCase['subject'], $case['subject']);
    }

    /**
     * @depends testCreate
     * @param integer $id
     */
    public function testDelete($id)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('orocrm_api_delete_case', ['id' => $id]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 204);
        $this->client->request(
            'GET',
            $this->getUrl('orocrm_api_get_case', ['id' => $id]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }
}
