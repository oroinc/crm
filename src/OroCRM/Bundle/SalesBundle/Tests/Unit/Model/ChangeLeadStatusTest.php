<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\Query\QueryBuilder;

use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\LeadStatus;

use OroCRM\Bundle\SalesBundle\Model\ChangeLeadStatus;

class ChangeLeadStatusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectManager
     */
    protected $entityManager;

    /**
     * @var ChangeLeadStatus
     */
    protected $model;

    public function setUp()
    {
        $this->entityManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
                              ->setMethods(['getReference', 'persist', 'flush'])
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->model = new ChangeLeadStatus($this->entityManager);
    }

    /**
     * @dataProvider actionDataProvider
     */
    public function testChangeStatus($statusCode)
    {
        $this->entityManager->expects($this->once())
            ->method('getReference')
            ->will($this->returnValue(new LeadStatus($statusCode)));

        $lead = new Lead();
        $this->model->disqualify($lead);
        $this->assertEquals($statusCode, $lead->getStatus()->getName());
    }

    public function actionDataProvider()
    {
        return [['canceled'], ['qualify']];
    }
}
