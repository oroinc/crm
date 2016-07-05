<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\Query\QueryBuilder;

use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\LeadStatus;

use OroCRM\Bundle\SalesBundle\Model\ChangeLeadStatus;

class ChangeLeadStatusTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Session
     */
    protected $session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectRepository
     */
    protected $objectRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var ChangeLeadStatus
     */
    protected $model;

    public function setUp()
    {
        $this->session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')
                        ->disableOriginalConstructor()
                        ->getMock();
        $this->objectManager = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
                              ->setMethods(['getRepository', 'persist', 'flush'])
                              ->disableOriginalConstructor()
                              ->getMock();
        
        $this->objectRepository = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
                                 ->setMethods([
                                     'find',
                                     'findAll',
                                     'findBy',
                                     'findOneBy',
                                     'getClassName',
                                     'findDefaultCalendar',
                                     'createQueryBuilder'])
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $this->queryBuilder = $this->getMockBuilder('Doctrine\DBAL\Query\QueryBuilder')
                             ->setMethods(['where', 'setParameter', 'getQuery', 'getSingleResult'])
                             ->disableOriginalConstructor()
                             ->getMock();

        $this->objectManager->expects($this->any())
                      ->method('getRepository')
                      ->will($this->returnValue($this->objectRepository));

        $this->objectRepository->expects($this->any())
                         ->method('createQueryBuilder')
                         ->will($this->returnValue($this->queryBuilder));

        $this->model = new ChangeLeadStatus($this->session, $this->objectManager);
    }


    public function testDisqualify()
    {
        $contactStatus = $this->getLeadStatus('canceled');

        $this->queryBuilder->method('where')->will($this->returnValue($this->queryBuilder));
        $this->queryBuilder->method('setParameter')->will($this->returnValue($this->queryBuilder));
        $this->queryBuilder->method('getQuery')->will($this->returnValue($this->queryBuilder));
        $this->queryBuilder->method('getSingleResult')->will($this->returnValue($contactStatus));

        $this->queryBuilder
                ->expects($this->any())
                ->method('getSingleResult')
                ->will($this->returnValue($contactStatus));

        $lead = new Lead();

        $this->model->disqualify($lead);

        $this->assertEquals('canceled', $lead->getStatus()->getName());

    }

    public function testQualify()
    {
        $contactStatus = $this->getLeadStatus('qualify');

        $this->queryBuilder->method('where')->will($this->returnValue($this->queryBuilder));
        $this->queryBuilder->method('setParameter')->will($this->returnValue($this->queryBuilder));
        $this->queryBuilder->method('getQuery')->will($this->returnValue($this->queryBuilder));
        $this->queryBuilder->method('getSingleResult')->will($this->returnValue($contactStatus));

        $this->queryBuilder
            ->expects($this->any())
            ->method('getSingleResult')
            ->will($this->returnValue($contactStatus));

        $lead = new Lead();

        $this->model->disqualify($lead);

        $this->assertEquals('qualify', $lead->getStatus()->getName());

    }

    /**
     * @param string $status
     * @return \PHPUnit_Framework_MockObject_MockObject|LeadStatus
     */
    protected function getLeadStatus($status)
    {
        $leadStatus = $this->getMockBuilder('OroCRM\Bundle\SalesBundle\Entity\LeadStatus')
                            ->setMethods(['getName'])
                            ->disableOriginalConstructor()
                            ->getMock();

        $leadStatus->method('getName')->will($this->returnValue($status));

        return $leadStatus;
    }
}
