<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Functional\Controller\Api\Soap;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\CaseBundle\Entity\CaseOrigin;
use OroCRM\Bundle\CaseBundle\Entity\CaseStatus;

/**
 * @outputBuffering enabled
 * @dbIsolation
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
        'origin'      => CaseOrigin::ORIGIN_EMAIL,
        'status'      => CaseStatus::STATUS_OPEN
    ];

    protected function setUp()
    {
        $this->initClient(array(), $this->generateWsseAuthHeader());
        $this->initSoapClient();
    }

    /**
     * @return integer
     */
    public function testCreate()
    {
        $result = $this->soapClient->createCase($this->case);
        $this->assertTrue((bool)$result, $this->soapClient->__getLastResponse());

        return $result;
    }

    /**
     * @depends testCreate
     */
    public function testCget()
    {
        $cases = $this->soapClient->getCases();
        $cases = $this->valueToArray($cases);
        $this->assertCount(1, $cases);
    }

    /**
     * @param integer $id
     * @depends testCreate
     */
    public function testGet($id)
    {
        $case = $this->soapClient->getCase($id);
        $case = $this->valueToArray($case);
        $this->assertEquals($this->case['subject'], $case['subject']);
    }

    /**
     * @param integer $id
     * @depends testCreate
     */
    public function testUpdate($id)
    {
        $case = array_merge($this->case, ['subject' => 'Updated subject']);

        $result = $this->soapClient->updateCase($id, $case);
        $this->assertTrue($result);

        $updatedCase = $this->soapClient->getCase($id);
        $updatedCase = $this->valueToArray($updatedCase);

        $this->assertEquals($case['subject'], $updatedCase['subject']);
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
