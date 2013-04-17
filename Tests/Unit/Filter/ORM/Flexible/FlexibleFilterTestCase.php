<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM\Flexible;

use Oro\Bundle\GridBundle\Filter\ORM\Flexible\AbstractFlexibleChildFilter;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

class FlexibleFilterTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractFlexibleChildFilter
     */
    protected $model;

    /**
     * @var string
     */
    protected $filterClass;

    /**
     * @var string
     */
    protected $parentFilterClass;

    protected function setUp()
    {
        $this->markTestSkipped();
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * @param array $arguments
     */
    protected function initializeFilter(array $arguments = array())
    {
        $defaultArguments = array(
            'flexibleRegistry' => $this->getMock('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry'),
            'parentFilter'     => $this->getMock($this->parentFilterClass, null, array(), '', false),
        );

        $arguments = array_merge($defaultArguments, $arguments);

        $filterClass = $this->filterClass;
        $this->model = new $filterClass($arguments['flexibleRegistry'], $arguments['parentFilter']);
    }

    /**
     * @param string $type
     * @param string $default
     * @param string $operator
     * @return AbstractFlexibleChildFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareParentFilterForGetOperator($type, $default, $operator)
    {
        $parentFilter = $this->getMock($this->parentFilterClass, array('getOperator'), array(), '', false);
        $parentFilter->expects($this->once())
            ->method('getOperator')
            ->with($type, $default)
            ->will($this->returnValue($operator));

        return $parentFilter;
    }

    /**
     * @param FlexibleEntityRepository $entityRepository
     * @param string $flexibleName
     * @return FlexibleManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareFlexibleRegistryForFilter(FlexibleEntityRepository $entityRepository, $flexibleName)
    {
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

        return $this->prepareFlexibleRegistry($flexibleManager, $flexibleName);
    }

    /**
     * @param FlexibleManager $flexibleManager
     * @param $flexibleName
     * @return FlexibleManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareFlexibleRegistry(FlexibleManager $flexibleManager, $flexibleName)
    {
        $flexibleRegistry = $this->getMock(
            'Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry',
            array('getManager')
        );
        $flexibleRegistry->expects($this->any())
            ->method('getManager')
            ->with($flexibleName)
            ->will($this->returnValue($flexibleManager));

        return $flexibleRegistry;
    }
}
