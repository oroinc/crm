<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Service;

use Doctrine\ORM\Query\Expr;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\MagentoBundle\DependencyInjection\Configuration;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery;
use Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery\DiscoveryStrategyInterface;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;

class AutomaticDiscoveryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DiscoveryStrategyInterface
     */
    protected $defaultStrategy;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|OwnershipMetadataProviderInterface
     */
    protected $metadataProvider;

    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @param array $config
     * @return AutomaticDiscovery
     */
    protected function getDiscovery(array $config)
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->defaultStrategy = $this
            ->createMock('Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery\DiscoveryStrategyInterface');
        $this->metadataProvider = $this
            ->getMockBuilder('Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityClass = 'Oro\Bundle\MagentoBundle\Entity\Customer';

        return new AutomaticDiscovery(
            $this->doctrineHelper,
            $this->defaultStrategy,
            $this->metadataProvider,
            $this->entityClass,
            $config
        );
    }

    public function testDiscoverSimilarNoConfig()
    {
        $this->assertNull($this->getDiscovery([])->discoverSimilar(new Customer()));
    }

    public function testDiscoverSimilarMatchAny()
    {
        $entity = new Customer();
        $id = null;
        $config = [
            Configuration::DISCOVERY_NODE => [
                Configuration::DISCOVERY_FIELDS_KEY => [
                    'test1' => null,
                    'test2' => null
                ],
                Configuration::DISCOVERY_STRATEGY_KEY => [
                    'test2' => 'any'
                ],
                Configuration::DISCOVERY_OPTIONS_KEY => [
                    Configuration::DISCOVERY_EMPTY_KEY => true,
                    Configuration::DISCOVERY_MATCH_KEY => Configuration::DISCOVERY_MATCH_FIRST
                ]
            ]
        ];

        $service = $this->getDiscovery($config);
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertDiscoveryCalls($qb, $entity, $id, $config, $service);

        $this->assertEquals($entity, $service->discoverSimilar($entity));
    }

    public function testDiscoverSimilarMatchLatest()
    {
        $entity = new Customer();
        $id = null;
        $config = [
            Configuration::DISCOVERY_NODE => [
                Configuration::DISCOVERY_FIELDS_KEY => [
                    'test1' => null,
                    'test2' => null
                ],
                Configuration::DISCOVERY_STRATEGY_KEY => [
                    'test2' => 'any'
                ],
                Configuration::DISCOVERY_OPTIONS_KEY => [
                    Configuration::DISCOVERY_EMPTY_KEY => true,
                    Configuration::DISCOVERY_MATCH_KEY => Configuration::DISCOVERY_MATCH_LATEST
                ]
            ]
        ];

        $service = $this->getDiscovery($config);
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb->expects($this->once())
            ->method('orderBy')
            ->with('e.id', 'DESC');

        $this->assertDiscoveryCalls($qb, $entity, $id, $config, $service);

        $this->assertEquals($entity, $service->discoverSimilar($entity));
    }

    public function testDiscoverSimilarMatchExisting()
    {
        $entity = new Customer();
        $id = 1;
        $config = [
            Configuration::DISCOVERY_NODE => [
                Configuration::DISCOVERY_FIELDS_KEY => [
                    'test1' => null,
                    'test2' => null
                ],
                Configuration::DISCOVERY_STRATEGY_KEY => [
                    'test2' => 'any'
                ],
                Configuration::DISCOVERY_OPTIONS_KEY => [
                    Configuration::DISCOVERY_EMPTY_KEY => true,
                    Configuration::DISCOVERY_MATCH_KEY => Configuration::DISCOVERY_MATCH_FIRST
                ]
            ]
        ];

        $service = $this->getDiscovery($config);

        $expr = new Expr();
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb->expects($this->any())
            ->method('expr')
            ->will($this->returnValue($expr));

        $this->assertDiscoveryCalls($qb, $entity, $id, $config, $service);

        $this->assertEquals($entity, $service->discoverSimilar($entity));
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $qb
     * @param object $entity
     * @param int|null $id
     * @param array $config
     * @param AutomaticDiscovery $service
     */
    protected function assertDiscoveryCalls($qb, $entity, $id, $config, AutomaticDiscovery $service)
    {
        $metadata = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadataProvider->expects($this->any())->method('getMetadata')->willReturn($metadata);
        $metadata->expects($this->any())->method('getOrganizationFieldName')->willReturn('organization');

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(['getOneOrNullResult'])
            ->getMockForAbstractClass();
        $query->expects($this->once())
            ->method('getOneOrNullResult')
            ->will($this->returnValue($entity));

        $qb->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));
        $qb->expects($this->any())
            ->method($this->anything())
            ->will($this->returnSelf());

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with(AutomaticDiscovery::ROOT_ALIAS)
            ->will($this->returnValue($qb));

        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifierFieldName')
            ->with($this->entityClass)
            ->will($this->returnValue('id'));
        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($entity)
            ->will($this->returnValue($id));

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->with($this->entityClass)
            ->will($this->returnValue($repository));

        $this->defaultStrategy->expects($this->once())
            ->method('apply')
            ->with($qb, AutomaticDiscovery::ROOT_ALIAS, 'test1', $config[Configuration::DISCOVERY_NODE], $entity);

        /** @var \PHPUnit\Framework\MockObject\MockObject|DiscoveryStrategyInterface $customStrategy */
        $customStrategy = $this
            ->createMock('Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery\DiscoveryStrategyInterface');
        $customStrategy->expects($this->once())
            ->method('apply')
            ->with($qb, AutomaticDiscovery::ROOT_ALIAS, 'test2', $config[Configuration::DISCOVERY_NODE], $entity);
        $service->addStrategy('test2', $customStrategy);
    }
}
