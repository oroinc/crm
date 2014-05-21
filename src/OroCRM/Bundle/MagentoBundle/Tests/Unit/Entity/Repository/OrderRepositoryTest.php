<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Entity\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\EntityBundle\Exception\InvalidEntityException;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\Repository\OrderRepository;

class OrderRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const ENTITY_NAME = 'OroCRMMagentoBundle:Order';

    /** @var OrderRepository */
    protected $repository;

    /** @var \PHPUnit_Framework_MockObject_MockObject|EntityManager */
    protected $em;

    protected function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->setMethods(['createQueryBuilder', 'beginTransaction', 'commit'])
            ->getMock();

        $this->repository = new OrderRepository(
            $this->em,
            new ClassMetadata(self::ENTITY_NAME)
        );
    }

    protected function tearDown()
    {
        unset($this->em, $this->repository);
    }

    /**
     * @dataProvider dataProvider()
     *
     * @param string $fields
     * @param Cart|Customer $item
     * @param bool $isException
     */
    public function testGetLastPlacedOrderBy($fields, $item, $isException)
    {
        $order = new Order();

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()->setMethods(['getOneOrNullResult'])
            ->getMockForAbstractClass();

        if (!$isException) {
            $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
                ->disableOriginalConstructor()
                ->setMethods(['setMaxResults', 'orderBy', 'where', 'setParameter', 'getQuery', 'select', 'from'])
                ->getMock();

            $queryBuilder->expects($this->once())->method('select')->with('o')
                ->will($this->returnSelf());
            $queryBuilder->expects($this->once())->method('from')->with(self::ENTITY_NAME, 'o')
                ->will($this->returnSelf());
            $queryBuilder->expects($this->once())->method('where')->with('o.'.$fields.' = :item')
                ->will($this->returnSelf());
            $queryBuilder->expects($this->once())->method('setParameter')->with('item', $item)
                ->will($this->returnSelf());
            $queryBuilder->expects($this->once())->method('orderBy')->with('o.updatedAt', 'DESC')
                ->will($this->returnSelf());
            $queryBuilder->expects($this->once())->method('setMaxResults')->with(1)
                ->will($this->returnSelf());
            $queryBuilder->expects($this->once())->method('getQuery')
                ->will($this->returnValue($query));

            $this->em->expects($this->once())->method('createQueryBuilder')
                ->will($this->returnValue($queryBuilder));

            $query->expects($this->once())->method('getOneOrNullResult')->will($this->returnValue($order));
        } else {
            $query
                ->expects($this->any())
                ->method('getOneOrNullResult')
                ->will($this->throwException(new InvalidEntityException()));
        }
        $result = $this->repository->getLastPlacedOrderBy($item, $fields);

        $this->assertSame($order, $result);
    }

    public function dataProvider()
    {
        return [
            ['cart', new Cart(), false],
            ['customer', new Customer(), false],
        ];
    }
}
