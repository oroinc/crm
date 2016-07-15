<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMInvalidArgumentException;

use OroCRM\Bundle\SalesBundle\Model\ChangeLeadStatus;
use OroCRM\Bundle\SalesBundle\Tests\Unit\Fixture\LeadStub;

class ChangeLeadStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ChangeLeadStatus
     */
    protected $model;

    /**
     * @var  LeadStub
     */
    private $lead;

    public function setUp()
    {
        $this->entityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
                              ->setMethods(['getReference', 'persist', 'flush'])
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->entityManager->expects($this->once())->method('getReference')
            ->will($this->returnCallback(function($statusClass, $statusCode){
                return $statusCode;
            }));

        $this->lead = new LeadStub();
        $this->model = new ChangeLeadStatus($this->entityManager);
    }

    public function testDisqualify()
    {
        $this->model->disqualify($this->lead);
        $this->assertEquals('canceled', $this->lead->getStatus());
    }

    public function testQualify()
    {
        $this->model->qualify($this->lead);
        $this->assertEquals('qualified', $this->lead->getStatus());
    }

    public function testSuccessQualify()
    {
        $this->assertTrue($this->model->qualify($this->lead));
    }

    public function testFailQualify()
    {
        $this->entityManager->expects($this->once())->method('persist')
            ->will($this->throwException(new ORMInvalidArgumentException('test exception')));

        $this->assertFalse($this->model->qualify($this->lead));
    }
}
