<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM\Flexible;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\GridBundle\Filter\ORM\Flexible\AbstractFlexibleDateFilter;
use Oro\Bundle\GridBundle\Form\Type\Filter\DateRangeType;
use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;
use Oro\Bundle\GridBundle\Filter\ORM\AbstractDateFilter;

class AbstractFlexibleDateFilterTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_ALIAS          = 'test_alias';
    const TEST_FIELD          = 'test_field';
    const TEST_DATE_START     = '2013-04-08';
    const TEST_DATE_END       = '2013-05-01';
    const TEST_NAME           = 'test_name';
    const TEST_FLEXIBLE_NAME  = 'test_flexible_entity';
    /**#@-*/

    /**
     * @var AbstractFlexibleDateFilter
     */
    protected $model;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var array
     */
    protected $actualFilterParameters = array();

    /**
     * @var AbstractDateFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $parentFilter;

    /**
     * @var array
     */
    protected $testOptions = array('test' => 'options');

    protected function tearDown()
    {
        unset($this->model);
        unset($this->queryBuilder);
        unset($this->parentFilter);
    }

    /**
     * @param array $arguments
     */
    protected function initializeFilter(array $arguments = array())
    {
        $this->parentFilter = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Filter\ORM\AbstractDateFilter',
            array(),
            '',
            false,
            true,
            true,
            array('getDefaultOptions', 'getRenderSettings', 'getTypeOptions')
        );
        $this->parentFilter->expects($this->any())
            ->method('getDefaultOptions')
            ->will($this->returnValue($this->testOptions));

        $defaultArguments = array(
            'flexibleRegistry' => $this->getMock('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry'),
            'parentFilter'     => $this->parentFilter,
        );

        $arguments = array_merge($defaultArguments, $arguments);

        $this->model = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Filter\ORM\Flexible\AbstractFlexibleDateFilter',
            array($arguments['flexibleRegistry'], $arguments['parentFilter'])
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parent filter must be an instance of Oro\Bundle\GridBundle\Filter\ORM\AbstractDateFilter
     */
    public function testConstructWithIncorrectParentFilter()
    {
        $incorrectFilter = $this->getMock('Oro\Bundle\GridBundle\Filter\FilterInterface');
        $this->initializeFilter(array('parentFilter' => $incorrectFilter));
    }

    /**
     * Data provider for testFilter
     *
     * @return array
     */
    public function filterDataProvider()
    {
        return array(
            'incorrect_parameters' => array(
                '$expected' => array(),
                '$data' => array(),
            ),
            'between' => array(
                '$expected' => array(
                    array(
                        'code'     => self::TEST_FIELD,
                        'value'    => self::TEST_DATE_START,
                        'operator' => '>=',
                    ),
                    array(
                        'code'     => self::TEST_FIELD,
                        'value'    => self::TEST_DATE_END,
                        'operator' => '<=',
                    ),
                ),
                '$data' => array(
                    'value' => array('start' => self::TEST_DATE_START, 'end' => self::TEST_DATE_END),
                    'type'  => DateRangeType::TYPE_BETWEEN
                ),
            ),
            'not_between' => array(
                '$expected' => array(
                    array(
                        'code'  => self::TEST_FIELD,
                        'value' => array(
                            'from' => self::TEST_DATE_START,
                            'to'   => self::TEST_DATE_END
                        ),
                        'operator' => array(
                            'from' => '<',
                            'to'   => '>',
                        ),
                    ),
                ),
                '$data' => array(
                    'value' => array('start' => self::TEST_DATE_START, 'end' => self::TEST_DATE_END),
                    'type'  => DateRangeType::TYPE_NOT_BETWEEN
                ),
            ),
        );
    }

    /**
     * @param array $expected
     * @param array $data
     *
     * @dataProvider filterDataProvider
     */
    public function testFilter(array $expected, array $data)
    {
        $this->queryBuilder = $this->getMock('Doctrine\ORM\QueryBuilder', array(), array(), '', false);
        $proxyQuery = new ProxyQuery($this->queryBuilder);

        $entityRepository = $this->getMock(
            'Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository',
            array('applyFilterByAttribute'),
            array(),
            '',
            false
        );
        $entityRepository->expects($this->any())
            ->method('applyFilterByAttribute')
            ->will($this->returnCallback(array($this, 'applyFilterByAttributeCallback')));

        $flexibleManager = $this->getMock(
            'Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager',
            array('getFlexibleRepository'),
            array(),
            '',
            false
        );
        $flexibleManager->expects($this->any())
            ->method('getFlexibleRepository')
            ->will($this->returnValue($entityRepository));

        $flexibleRegistry = $this->getMock(
            'Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry',
            array('getManager')
        );
        $flexibleRegistry->expects($this->any())
            ->method('getManager')
            ->with(self::TEST_FLEXIBLE_NAME)
            ->will($this->returnValue($flexibleManager));

        $this->initializeFilter(array('flexibleRegistry' => $flexibleRegistry));
        $this->model->initialize(self::TEST_NAME, array('flexible_name' => self::TEST_FLEXIBLE_NAME));
        $this->model->filter($proxyQuery, self::TEST_ALIAS, self::TEST_FIELD, $data);

        $this->assertEquals($expected, $this->actualFilterParameters);
    }

    /**
     * Callback for FlexibleEntityRepository::applyFilterByAttribute
     *
     * @param QueryBuilder $queryBuilder
     * @param string|array $attributeCode
     * @param string|array $attributeValue
     * @param string|array $operator
     */
    public function applyFilterByAttributeCallback(
        QueryBuilder $queryBuilder,
        $attributeCode,
        $attributeValue,
        $operator
    ) {
        $this->assertEquals($this->queryBuilder, $queryBuilder);
        $this->actualFilterParameters[] = array(
            'code'     => $attributeCode,
            'value'    => $attributeValue,
            'operator' => $operator
        );
    }

    public function testGetDefaultOptions()
    {
        $this->initializeFilter();
        $this->assertEquals($this->testOptions, $this->model->getDefaultOptions());
    }

    public function testGetRenderSettings()
    {
        $this->initializeFilter();

        $this->parentFilter->expects($this->once())
            ->method('getRenderSettings')
            ->will($this->returnValue($this->testOptions));

        $this->assertEquals($this->testOptions, $this->model->getRenderSettings());
    }

    public function testGetTypeOptions()
    {
        $this->initializeFilter();

        $this->parentFilter->expects($this->once())
            ->method('getTypeOptions')
            ->will($this->returnValue($this->testOptions));

        $this->assertEquals($this->testOptions, $this->model->getTypeOptions());
    }
}
