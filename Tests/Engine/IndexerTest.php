<?php
namespace Oro\Bundle\SearchBundle\Test\Engine;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SearchBundle\Engine\Indexer;

class IndexerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Oro\Bundle\SearchBundle\Engine\Indexer
     */
    protected $indexService;
    protected $om;
    protected $repository;
    protected $connector;

    public function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ObjectManager')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $this->om->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('OroTestBundle:test'))
            ->will($this->returnValue($this->repository));

        $this->connector = $this->getMockForAbstractClass(
            'Oro\Bundle\SearchBundle\Engine\AbstractEngine',
            array(
                 $this->om
            )
        );

        $this->connector->expects($this->any())
            ->method('doSearch')
            ->will($this->returnValue(array('results' => array(), 'records_count' => 10)));

        $this->connector->expects($this->any())
            ->method('searchQuery')
            ->will($this->returnValue(array()));

        $this->indexService = new Indexer($this->om, $this->connector, array(
            'Oro\Bundle\DataBundle\Entity\Product' => array(
                'fields' => array(
                    array(
                        'name' => 'name',
                        'target_type' => 'string',
                        'target_fields' => array('name', 'all_data')
                    ),
                    array(
                        'name' => 'description',
                        'target_type' => 'string',
                        'target_fields' => array('description', 'all_data')
                    ),
                    array(
                        'name' => 'price',
                        'target_type' => 'decimal',
                        'target_fields' => array('price')
                    ),
                    array(
                        'name' => 'count',
                        'target_type' => 'integer',
                        'target_fields' => array('count')
                    ),
                    array(
                        'name' => 'createDate',
                        'target_type' => 'datetime',
                        'target_fields' => array('create_date')
                    ),
                    array(
                        'name' => 'manufacturer',
                        'relation_type' => 'to',
                        'relation_fields' => array(
                            array(
                                'name' => 'name',
                                'target_type' => 'string',
                                'target_fields' => array('manufacturer', 'all_data')
                            )
                        )
                    ),
                )
            )
        ));
    }

    /**
     * Get query builder with select instance
     */
    public function testSelect()
    {
        $select = $this->indexService->select();
        $this->assertEquals('select', $select->getQuery());
    }

    /**
     * Run query with query builder
     */
    public function testQuery()
    {
        $select = $this->indexService->select();

        $this->connector->expects($this->once())
            ->method('doSearch')
            ->with($select)
            ->will($this->returnValue(array()));

        $this->indexService->query($select);
    }
}