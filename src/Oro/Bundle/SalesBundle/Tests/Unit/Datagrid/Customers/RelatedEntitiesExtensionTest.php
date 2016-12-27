<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Datagrid\Extension\Customers;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

use Oro\Bundle\EntityBundle\Tests\Unit\ORM\Fixtures\TestEntity;

use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use Oro\Bundle\SalesBundle\Datagrid\Extension\Customers\RelatedEntitiesExtension;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\EntityConfig\CustomerScope;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;

class RelatedEntitiesExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var RelatedEntitiesExtension */
    protected $extension;

    /** @var ConfigProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $configProvider;

    public function setUp()
    {
        $this->configProvider = $this
            ->getMockBuilder('Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider')
            ->disableOriginalConstructor()
            ->setMethods(['isCustomerClass'])
            ->getMock();

        $this->extension = new RelatedEntitiesExtension(
            $this->configProvider,
            Opportunity::class
        );
    }

    /**
     * @dataProvider testIsApplicableDataProvider
     *
     * @param array     $config
     * @param array     $parameters
     * @param bool      $result
     * @param bool|null $enabledConfig
     */
    public function testIsApplicable(array $config, array $parameters, $result, $enabledConfig = null)
    {
        $this->extension->setParameters(new ParameterBag($parameters));
        $this->prepareConfigProvider($parameters, $enabledConfig);
        $this->assertEquals(
            $result,
            $this->extension->isApplicable(DatagridConfiguration::create($config))
        );
    }

    public function testIsApplicableDataProvider()
    {
        $class = TestEntity::class;
        $relatedClass = Opportunity::class;
        return [
            'not orm source type'           => [
                ['source' => ['type' => 'not_orm']],
                [],
                false
            ],
            'no customer_class param'       => [
                ['source' => ['type' => 'orm']],
                ['customer_id' => 1, 'related_entity_class' => $relatedClass],
                false
            ],
            'no customer_id param'          => [
                ['source' => ['type' => 'orm']],
                ['customer_class' => 'test', 'related_entity_class' => $relatedClass],
                false
            ],
            'empty customer_class param'    => [
                ['source' => ['type' => 'orm']],
                ['customer_id' => 1, 'customer_class' => '', 'related_entity_class' => $relatedClass],
                false
            ],
            'empty customer_id param'       => [
                ['source' => ['type' => 'orm']],
                ['customer_class' => $class, 'customer_id' => null, 'related_entity_class' => $relatedClass],
                false
            ],
            'not supported customer class'  => [
                ['source' => ['type' => 'orm']],
                ['customer_class' => $class, 'customer_id' => 1, 'related_entity_class' => $relatedClass],
                false,
                false
            ],
            'invalid related entity class' => [
                ['source' => ['type' => 'orm']],
                ['customer_class' => $class, 'customer_id' => 1, 'related_entity_class' => Lead::class],
                false
            ],
            'all parameters and config set' => [
                ['source' => ['type' => 'orm']],
                ['customer_class' => $class, 'customer_id' => 1, 'related_entity_class' => $relatedClass],
                true,
                true
            ]
        ];
    }

    public function testVisitDatasource()
    {
        $customerClass   = TestEntity::class;
        $customerField   = ExtendHelper::buildAssociationName(
            $customerClass,
            CustomerScope::ASSOCIATION_KIND
        );
        $customerId = 1;
        $customerIdParam = sprintf(':customerIdParam_%s', $customerField);
        $qb = $this->prepareQueryBuilder(Opportunity::class, $customerField, $customerId, $customerIdParam, 'customer');
        $datasource      = $this->getDatasource($qb);
        $config          = DatagridConfiguration::create([]);

        $this->extension->setParameters(
            new ParameterBag(
                [
                    'customer_class' => $customerClass,
                    'customer_id' => $customerId,
                    'related_entity_class' => Opportunity::class
                ]
            )
        );
        $this->extension->visitDatasource($config, $datasource);
    }

    /**
     * @expectedException \Oro\Bundle\DataGridBundle\Exception\DatasourceException
     * @expectedExceptionMessage Couldn't find Oro\Bundle\SalesBundle\Entity\Opportunity alias in QueryBuilder.
     */
    public function testVisitDatasourceNotFoundOpportunityFrom()
    {
        $qb            = $this->prepareQueryBuilder(TestEntity::class);
        $datasource    = $this->getDatasource($qb);
        $config        = DatagridConfiguration::create([]);
        $this->extension->setParameters(
            new ParameterBag(['customer_class' => TestEntity::class])
        );
        $this->extension->visitDatasource($config, $datasource);
    }

    /**
     * @param bool|null $enabledConfig
     * @param array     $parameters
     */
    protected function prepareConfigProvider(array $parameters, $enabledConfig = null)
    {
        if ($enabledConfig !== null) {
            $this->configProvider
                ->expects($this->once())
                ->method('isCustomerClass')
                ->with($parameters['customer_class'])
                ->willReturn($enabledConfig);
        }
    }

    /**
     * @param string      $opportunityClass
     * @param string      $customerField
     * @param int|null    $customerId
     * @param string|null $customerIdParam
     * @param string|null $alias
     *
     * @return QueryBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    public function prepareQueryBuilder(
        $opportunityClass,
        $customerField = null,
        $customerId = null,
        $customerIdParam = null,
        $alias = null
    ) {
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb->expects($this->never())
            ->method('getDQLPart');
        $from = $this->getMockBuilder('Doctrine\ORM\Query\Expr\From')
            ->disableOriginalConstructor()
            ->getMock();
        $from->expects($this->once())
            ->method('getFrom')
            ->will($this->returnValue($opportunityClass));
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb->expects($this->once())
            ->method('getDQLPart')
            ->with('from')
            ->will($this->returnValue([$from]));
        if (null !== $alias) {
            $from->expects($this->once())
                ->method('getAlias')
                ->will($this->returnValue($alias));
            $qb->expects($this->once())
                ->method('andWhere')
                ->with(sprintf('%s.%s = %s', $alias, $customerField, $customerIdParam))
                ->will($this->returnSelf());
            $qb->expects($this->once())
                ->method('setParameter')
                ->with($customerIdParam, $customerId);
        }

        return $qb;
    }

    /**
     * @param QueryBuilder|\PHPUnit_Framework_MockObject_MockObject $qb
     *
     * @return OrmDatasource|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getDatasource($qb)
    {
        $datasource = $this->getMockBuilder('Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource')
            ->disableOriginalConstructor()
            ->getMock();
        $datasource->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($qb);

        return $datasource;
    }
}
