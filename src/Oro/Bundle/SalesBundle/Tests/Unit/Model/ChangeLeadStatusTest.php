<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Symfony\Component\Validator\Validator\ValidatorInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMInvalidArgumentException;

use Oro\Bundle\SalesBundle\Model\ChangeLeadStatus;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\LeadStub;

class ChangeLeadStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ValidatorInterface
     */
    protected $validator;

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
            ->will($this->returnCallback(function ($statusClass, $statusCode) {
                return $statusCode;
            }));
        
        $this->validator = $this->getMockForAbstractClass('Symfony\Component\Validator\Validator\ValidatorInterface');
        $this->validator->expects($this->any())->method('validate')
            ->willReturn($this->getMockForAbstractClass('\Countable'));

        $this->lead = new LeadStub();
        $this->model = new ChangeLeadStatus($this->entityManager, $this->validator);
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
