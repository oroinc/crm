<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Engine;

use Oro\Bundle\SearchBundle\Engine\Orm;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Entity\Item;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

use Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product;
use Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Manufacturer;
use Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Attribute;


class OrmTest extends \PHPUnit_Framework_TestCase
{
    private $product;
    /**
     * @var \Oro\Bundle\SearchBundle\Engine\Orm
     */
    private $orm;
    private $mappingConfig;
    private $om;
    private $container;
    private $translator;
    private $flexibleManager;

    public function setUp()
    {
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->mappingConfig =  array(
            'Oro\Bundle\SearchBundle\Tests\Unit\Fixture\Entity\Product' => array(
                'alias' => 'test_product',
                'label' => 'test product',
                'title_fields' => array('name'),
                'route' => array(
                    'name' => 'test_route',
                    'parameters' => array()
                ),
                'fields' => array(
                    array(
                        'name'          => 'name',
                        'target_type'   => 'text',
                        'target_fields' => array(
                            'name',
                            'all_data'
                        )
                    ),
                    array(
                        'name'          => 'description',
                        'target_type'   => 'text',
                        'target_fields' => array(
                            'description',
                            'all_data'
                        )
                    ),
                    array(
                        'name'          => 'price',
                        'target_type'   => 'decimal',
                        'target_fields' => array('price')
                    ),
                    array(
                        'name'          => 'count',
                        'target_type'   => 'integer',
                        'target_fields' => array('count')
                    ),
                    array(
                        'name'            => 'manufacturer',
                        'relation_type'   => 'one-to-one',
                        'relation_fields' => array(
                            array(
                                'name'          => 'name',
                                'target_type'   => 'text',
                                'target_fields' => array(
                                    'manufacturer',
                                    'all_data'
                                )
                            )
                        )
                    ),
                ),
                'flexible_manager' => 'test_manager'
            )
        );

        $manufacturer = new Manufacturer();
        $manufacturer->setName('adidas');

        $this->product = new Product();
        $this->product->setName('test product')
            ->setCount(10)
            ->setPrice(150)
            ->setManufacturer($manufacturer)
            ->setDescription('description')
            ->setCreateDate(new \DateTime());

        $this->flexibleManager = $this
            ->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $this->flexibleManager->expects($this->any())
            ->method('getAttributeRepository')
            ->will($this->returnValue($this->attributeRepository));

        $this->translator =  $this->getMockBuilder('Symfony\Component\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->translator->expects($this->any())
            ->method('trans')
            ->will($this->returnValue('translated string'));

        $this->route = $this
            ->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();

        $this->route->expects($this->any())
            ->method('generate')
            ->will($this->returnValue('http://example.com'));

        $this->container->expects($this->any())
            ->method('get')
            ->with($this->logicalOr(
                $this->equalTo('translator'),
                $this->equalTo('test_manager'),
                $this->equalTo('router')
            ))
            ->will($this->returnCallback(
                function($param) {
                    switch ($param) {
                        case 'translator':
                            return $this->translator;
                            break;
                        case 'test_manager':
                            return $this->flexibleManager;
                            break;
                        case 'router':
                            return $this->route;
                            break;
                    }
                }
            ));


        $this->orm = new Orm($this->om, $this->container, $this->mappingConfig, false);
    }

    public function testMapObject()
    {
        $testTextAttribute = new Attribute();
        $testTextAttribute->setCode('text_attribute')
            ->setBackendType(AbstractAttributeType::BACKEND_TYPE_TEXT);

        $testIntegerAttribute = new Attribute();
        $testIntegerAttribute->setCode('integer_attribute')
            ->setBackendType(AbstractAttributeType::BACKEND_TYPE_INTEGER);

        $testDatetimeAttribute = new Attribute();
        $testDatetimeAttribute->setCode('datetime_attribute')
            ->setBackendType(AbstractAttributeType::BACKEND_TYPE_DATETIME);

        $this->attributeRepository->expects($this->once())
            ->method('findBy')
            ->will($this->returnValue(
                array(
                     $testTextAttribute,
                     $testIntegerAttribute,
                     $testDatetimeAttribute
                )
            ));

        $mapping = $this->orm->mapObject($this->product);

        $this->assertEquals('test product ', $mapping['text']['name']);
        $this->assertEquals(150, $mapping['decimal']['price']);
        $this->assertEquals(10, $mapping['integer']['count']);
        $this->assertEquals(' text_attribute', $mapping['text']['text_attribute']);
    }

    public function testDoSearch()
    {
        $query = new Query();
        $query->createQuery(Query::SELECT)
            ->from('test')
            ->andWhere('name', '~', 'test value', Query::TYPE_TEXT);

        $searchRepo = $this
            ->getMockBuilder('Oro\Bundle\SearchBundle\Entity\Repository\SearchIndexRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->om->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('OroSearchBundle:Item'))
            ->will($this->returnValue($searchRepo));

        $this->container->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('oro_search.engine_orm'))
            ->will($this->returnValue('test_orm'));

        $searchRepo->expects($this->once())
            ->method('setDriversClasses');

        $result = $this->orm->search($query);

        $this->assertEquals(0, $result->getRecordsCount());
        $searchOptions = $result->getQuery()->getOptions();

        $this->assertEquals('name', $searchOptions[0]['fieldName']);
        $this->assertEquals(Query::OPERATOR_CONTAINS, $searchOptions[0]['condition']);
        $this->assertEquals('test value', $searchOptions[0]['fieldValue']);
        $this->assertEquals(Query::TYPE_TEXT, $searchOptions[0]['fieldType']);
        $this->assertEquals(Query::KEYWORD_AND, $searchOptions[0]['type']);
    }

    public function testDelete()
    {
        $searchRepo = $this
            ->getMockBuilder('Oro\Bundle\SearchBundle\Entity\Repository\SearchIndexRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->om->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('OroSearchBundle:Item'))
            ->will($this->returnValue($searchRepo));

        $this->container->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('oro_search.engine_orm'))
            ->will($this->returnValue('test_orm'));

        $searchRepo->expects($this->once())
            ->method('setDriversClasses');

        $item = new Item();

        $searchRepo->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($item));

        $this->om->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($item));

        $this->om->expects($this->once())
            ->method('flush');

        $this->orm->delete($this->product, true);
    }

    public function testSave()
    {
        $searchRepo = $this
            ->getMockBuilder('Oro\Bundle\SearchBundle\Entity\Repository\SearchIndexRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->om->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('OroSearchBundle:Item'))
            ->will($this->returnValue($searchRepo));

        $this->container->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('oro_search.engine_orm'))
            ->will($this->returnValue('test_orm'));

        $searchRepo->expects($this->once())
            ->method('setDriversClasses');

        $this->orm->save($this->product, true);
    }
}
