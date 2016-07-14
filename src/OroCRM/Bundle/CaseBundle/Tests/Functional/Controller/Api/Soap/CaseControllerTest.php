<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Functional\Controller\Api\Soap;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\CaseBundle\Entity\CasePriority;
use OroCRM\Bundle\CaseBundle\Entity\CaseSource;
use OroCRM\Bundle\CaseBundle\Entity\CaseStatus;

/**
 * @outputBuffering enabled
 * @dbIsolation
 * @group soap
 */
class CaseControllerTest extends WebTestCase
{
    /**
     * @var array
     */
    protected $caseCreateData = [
        'subject'     => 'New case',
        'description' => 'New description',
        'owner'       => 1,
    ];

    /**
     * @var int
     */
    protected static $adminUserId = 1;

    /**
     * @var int
     */
    protected static $contactId;

    protected function setUp()
    {
        $this->initClient(array(), $this->generateWsseAuthHeader());

        $this->loadFixtures(['OroCRM\Bundle\CaseBundle\Tests\Functional\DataFixtures\LoadCaseEntityData']);
        $this->initSoapClient();
    }

    protected function postFixtureLoad()
    {
        $contact = $this->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('OroCRMContactBundle:Contact')
            ->findOneByEmail('daniel.case@example.com');

        $this->assertNotNull($contact);

        self::$contactId = $contact->getId();
    }

    /**
     * @return integer
     */
    public function testCreate()
    {
        $result = $this->soapClient->createCase($this->caseCreateData);
        $this->assertGreaterThan(0, $result, $this->soapClient->__getLastResponse());

        return $result;
    }

    /**
     * @depends testCreate
     * @param integer $id
     */
    public function testCget($id)
    {
        $result = $this->soapClient->getCases();
        $result = $this->valueToArray($result);
        $cases = $result['item'];

        $this->assertCount(4, $cases);

        $this->assertArrayIntersectEquals(
            array(
                'id' => $id,
                'subject' => $this->caseCreateData['subject'],
                'description' => $this->caseCreateData['description'],
                'owner' => self::$adminUserId,
                'relatedContact' => null,
                'relatedAccount' => null,
                'source' => CaseSource::SOURCE_OTHER,
                'status' => CaseStatus::STATUS_OPEN,
                'priority' => CasePriority::PRIORITY_NORMAL,
                'updatedAt' => null,
                'closedAt' => null,
            ),
            $cases[0]
        );

        $this->assertNotEmpty($cases[0]['createdAt']);
        $this->assertNotEmpty($cases[0]['reportedAt']);
    }

    /**
     * @depends testCreate
     * @param integer $id
     * @return array
     */
    public function testGet($id)
    {
        $result = $this->soapClient->getCase($id);
        $case = $this->valueToArray($result);

        $this->assertArrayIntersectEquals(
            array(
                'id' => $id,
                'subject' => $this->caseCreateData['subject'],
                'description' => $this->caseCreateData['description'],
                'owner' => self::$adminUserId,
                'relatedContact' => null,
                'relatedAccount' => null,
                'source' => CaseSource::SOURCE_OTHER,
                'status' => CaseStatus::STATUS_OPEN,
                'priority' => CasePriority::PRIORITY_NORMAL,
                'updatedAt' => null,
                'closedAt' => null,
            ),
            $case
        );

        $this->assertNotEmpty($case['createdAt']);
        $this->assertNotEmpty($case['reportedAt']);

        return $case;
    }

    /**
     * @depends testGet
     * @param array $originalCase
     * @return integer
     */
    public function testUpdate(array $originalCase)
    {
        $id = $originalCase['id'];

        $updateData = [
            'subject' => 'Updated subject',
            'description' => 'Updated description',
            'resolution' => 'Updated resolution',
            'status' => CaseStatus::STATUS_CLOSED,
            'priority' => CasePriority::PRIORITY_LOW,
            'source' => CaseSource::SOURCE_WEB,
            'relatedContact' => self::$contactId,
            'assignedTo' => self::$adminUserId,
        ];

        $result = $this->soapClient->updateCase($id, $updateData);
        $this->assertTrue($result, $this->soapClient->__getLastResponse());

        $updatedCase = $this->soapClient->getCase($id);
        $updatedCase = $this->valueToArray($updatedCase);

        $this->assertNotEmpty($updatedCase['updatedAt']);
        $this->assertNotEmpty($updatedCase['closedAt']);

        $expectedCase = array_merge($originalCase, $updateData);
        $expectedCase['updatedAt'] = $updatedCase['updatedAt'];
        $expectedCase['closedAt'] = $updatedCase['closedAt'];

        return $id;
    }

    /**
     * @param integer $id
     * @depends testCreate
     */
    public function testDelete($id)
    {
        $result = $this->soapClient->deleteCase($id);
        $this->assertTrue($result);

        $this->setExpectedException('\SoapFault', 'Record with ID "' . $id . '" can not be found');
        $this->soapClient->getCase($id);
    }
}
