<?php

namespace Oro\Bundle\SearchBundle\Test\Engine;

use Oro\Bundle\SearchBundle\Engine\Orm;
use Oro\Bundle\SearchBundle\Tests\Fixture\Entity\Product;
use Oro\Bundle\SearchBundle\Tests\Fixture\Entity\Manufacturer;

class OrmTest extends \PHPUnit_Framework_TestCase
{
    private $product;
    private $orm;
    private $mappingConfig;
    private $om;
    private $container;

    public function setUp()
    {
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->container = $this->getMockForAbstractClass('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->mappingConfig =  array(
            'Oro\Bundle\SearchBundle\Tests\Fixture\Entity\Product' => array(
                'fields' => array(
                    array(
                        'name'          => 'name',
                        'target_type'   => 'string',
                        'target_fields' => array(
                            'name',
                            'all_data'
                        )
                    ),
                    array(
                        'name'          => 'description',
                        'target_type'   => 'string',
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
                                'target_type'   => 'string',
                                'target_fields' => array(
                                    'manufacturer',
                                    'all_data'
                                )
                            )
                        )
                    ),
                )
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

        $this->orm = new Orm($this->om, $this->container, $this->mappingConfig);
    }

    public function testMapObject()
    {
        $mapping = $this->orm->mapObject($this->product);

        $this->assertEquals('test product', $mapping['string']['name']);
        $this->assertEquals(150, $mapping['decimal']['price']);
        $this->assertEquals(10, $mapping['integer']['count']);
    }

    /*public function testLogSearch()
    {
        $logger = new QueryLogger($this->om);

        $this->om->expects($this->once())->method('persist');
        $this->om->expects($this->once())->method('flush');

        $logger->logSearch(new Query(), array());
    }*/
}
