<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\Query\QueryBuilder;

use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;
use OroCRM\Bundle\SalesBundle\Model\B2bGuesser;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

class B2bGuesserTest extends \PHPUnit_Framework_TestCase
{

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
     * @var \PHPUnit_Framework_MockObject_MockObject|EntityFieldProvider
     */
    protected $entityFieldProvider;

    /**
     * @var B2bGuesser
     */
    protected $model;

    public function setUp()
    {
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
            ->setMethods(['where', 'setParameter', 'getQuery', 'getSingleResult', 'expr', 'orX', 'eq'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->objectRepository));

        $this->objectRepository->expects($this->any())
            ->method('createQueryBuilder')
            ->will($this->returnValue($this->queryBuilder));

        $this->entityFieldProvider = $this->getMockBuilder('Oro\Bundle\EntityBundle\Provider\EntityFieldProvider')
            ->setMethods(['getFields'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new B2bGuesser($this->objectManager, $this->entityFieldProvider);
    }

    public function testGetCustomerIfLeadHaveCustomer()
    {
        $leadMock = $this->getMockBuilder('OroCRM\Bundle\SalesBundle\Entity\Lead')
                            ->setMethods(['getCustomer'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $leadMock->expects($this->once())
                 ->method('getCustomer')
                 ->willReturn(new B2bCustomer());

        $result = $this->model->getCustomer($leadMock);

        $this->assertTrue(($result instanceof B2bCustomer));
    }
}