<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Functional\Controller\Api\Rest;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\CaseBundle\Entity\CaseSource;
use OroCRM\Bundle\CaseBundle\Entity\CaseStatus;
use OroCRM\Bundle\CaseBundle\Entity\CasePriority;

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
    protected $casePostData = [
        'subject' => 'New case',
        'description' => 'New description',
        'resolution' => 'New resolution',
    ];

    /**
     * @var int
     */
    protected static $contactId;

    /**
     * @var int
     */
    protected static $adminUserId = 1;

    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader());

        $this->loadFixtures(['OroCRM\Bundle\CaseBundle\Tests\Functional\DataFixtures\LoadCaseEntityData']);
    }

    protected function postFixtureLoad()
    {
        $contact = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('OroCRMContactBundle:Contact')
            ->findOneByEmail('daniel.case@example.com');

        $this->assertNotNull($contact);

        self::$contactId = $contact->getId();
    }

    public function testCreate()
    {
        $request = [
            'case' => $this->casePostData
        ];

        $this->client->request(
            'POST',
            $this->getUrl('orocrm_case_api_post_case'),
            $request
        );

        $response = $this->getJsonResponseContent($this->client->getResponse(), 201);

        return $response['id'];
    }

    /**
     * @depends testCreate
     */
    public function testCget()
    {
        $this->client->request(
            'GET',
            $this->getUrl('orocrm_case_api_get_cases'),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $cases = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCount(4, $cases);

        $this->assertCaseDataEquals(
            [
                'subject' => $this->casePostData['subject'],
                'description' => $this->casePostData['description'],
                'status' => CaseStatus::STATUS_OPEN,
                'priority' => CasePriority::PRIORITY_NORMAL,
                'source' => CaseSource::SOURCE_OTHER,
                'relatedContact' => null,
                'relatedAccount' => null,
                'assignedTo' => null,
                'owner' => self::$adminUserId,
                'updatedAt' => null,
                'closedAt' => null,
            ],
            $cases[3]
        );
    }

    /**
     * @depends testCreate
     * @param integer $id
     * @return array
     */
    public function testGet($id)
    {
        $this->client->request(
            'GET',
            $this->getUrl('orocrm_case_api_get_case', ['id' => $id]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );

        $case = $this->getJsonResponseContent($this->client->getResponse(), 200);

        $this->assertCaseDataEquals(
            [
                'subject' => $this->casePostData['subject'],
                'description' => $this->casePostData['description'],
                'status' => CaseStatus::STATUS_OPEN,
                'priority' => CasePriority::PRIORITY_NORMAL,
                'source' => CaseSource::SOURCE_OTHER,
                'relatedContact' => null,
                'relatedAccount' => null,
                'assignedTo' => null,
                'owner' => self::$adminUserId,
                'updatedAt' => null,
                'closedAt' => null,
            ],
            $case
        );

        return $case;
    }

    /**
     * @depends testGet
     * @param array $originalCase
     * @return integer
     */
    public function testPut(array $originalCase)
    {
        $id = $originalCase['id'];

        $putData = [
            'subject' => 'Updated subject',
            'description' => 'Updated description',
            'resolution' => 'Updated resolution',
            'status' => CaseStatus::STATUS_CLOSED,
            'priority' => CasePriority::PRIORITY_LOW,
            'source' => CaseSource::SOURCE_WEB,
            'relatedContact' => self::$contactId,
            'assignedTo' => self::$adminUserId,
        ];

        $this->client->request(
            'PUT',
            $this->getUrl('orocrm_case_api_put_case', ['id' => $id]),
            ['case' => $putData],
            [],
            $this->generateWsseAuthHeader()
        );

        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);

        $this->client->request(
            'GET',
            $this->getUrl('orocrm_case_api_get_case', ['id' => $id])
        );

        $updatedCase = $this->getJsonResponseContent($this->client->getResponse(), 200);
        $this->assertNotEmpty($updatedCase['updatedAt']);
        $this->assertNotEmpty($updatedCase['closedAt']);

        $expectedCase = array_merge($originalCase, $putData);
        $expectedCase['updatedAt'] = $updatedCase['updatedAt'];
        $expectedCase['closedAt'] = $updatedCase['closedAt'];

        $this->assertCaseDataEquals($expectedCase, $updatedCase);

        return $id;
    }

    /**
     * @depends testPut
     * @param integer $id
     * @return integer
     */
    public function testDelete($id)
    {
        $this->client->request(
            'DELETE',
            $this->getUrl('orocrm_case_api_delete_case', ['id' => $id]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );
        $result = $this->client->getResponse();
        $this->assertEmptyResponseStatusCodeEquals($result, 204);
        $this->client->request(
            'GET',
            $this->getUrl('orocrm_case_api_get_case', ['id' => $id]),
            [],
            [],
            $this->generateWsseAuthHeader()
        );
        $result = $this->client->getResponse();
        $this->assertJsonResponseStatusCodeEquals($result, 404);
    }

    /**
     * @param array $expected
     * @param array $actual
     */
    protected function assertCaseDataEquals(array $expected, array $actual)
    {
        $this->assertArrayHasKey('id', $actual);
        $this->assertGreaterThan(0, $actual['id']);
        $this->assertInternalType('integer', $actual['id']);

        $this->assertArrayHasKey('subject', $actual);
        $this->assertArrayHasKey('description', $actual);
        $this->assertArrayHasKey('resolution', $actual);

        $this->assertArrayHasKey('source', $actual);
        $this->assertNotEmpty($actual['source']);

        $this->assertArrayHasKey('status', $actual);
        $this->assertNotEmpty($actual['status']);

        $this->assertArrayHasKey('priority', $actual);
        $this->assertNotEmpty($actual['priority']);

        $this->assertArrayHasKey('relatedContact', $actual);
        $this->assertArrayHasKey('relatedAccount', $actual);
        $this->assertArrayHasKey('assignedTo', $actual);

        $this->assertArrayHasKey('owner', $actual);
        $this->assertGreaterThan(0, $actual['owner']);
        $this->assertInternalType('integer', $actual['owner']);

        $this->assertArrayHasKey('createdAt', $actual);
        $this->assertNotEmpty($actual['createdAt']);

        $this->assertArrayHasKey('updatedAt', $actual);

        $this->assertArrayHasKey('reportedAt', $actual);
        $this->assertNotEmpty($actual['reportedAt']);

        $this->assertArrayHasKey('closedAt', $actual);

        $this->assertArrayIntersectEquals($expected, $actual);
    }
}
