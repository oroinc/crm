<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM\Flexible;

use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;
use Oro\Bundle\GridBundle\Filter\ORM\Flexible\AbstractFlexibleDateFilter;
use Oro\Bundle\GridBundle\Filter\ORM\DateRangeFilter;

class AbstractFlexibleDateFilterTest extends FlexibleFilterTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function createTestFilter($flexibleRegistry)
    {
        $parentFilter = new DateRangeFilter($this->getTranslatorMock());
        return $this->getMockBuilder('Oro\Bundle\GridBundle\Filter\ORM\Flexible\AbstractFlexibleDateFilter')
            ->setConstructorArgs(array($flexibleRegistry, $parentFilter))
            ->getMockForAbstractClass();
    }

    public function filterDataProvider()
    {
        return array(
            'no_data' => array(
                'data' => array(),
                'expectRepositoryCalls' => array()
            ),
            'between' => array(
                'data' => array(
                    'value' => array(
                        'start' => new \DateTime('2013-01-01'),
                        'end' => new \DateTime('2014-01-01'),
                    ),
                    'type' => DateRangeFilterType::TYPE_BETWEEN
                ),
                'expectRepositoryCalls' => array(
                    array('applyFilterByAttribute', array(self::TEST_FIELD, '2013-01-01', '>='), null),
                    array('applyFilterByAttribute', array(self::TEST_FIELD, '2014-01-01', '<='), null),
                )
            ),
            'between_only_start' => array(
                'data' => array(
                    'value' => array(
                        'start' => new \DateTime('2013-01-01'),
                    ),
                    'type' => DateRangeFilterType::TYPE_BETWEEN
                ),
                'expectRepositoryCalls' => array(
                    array('applyFilterByAttribute', array(self::TEST_FIELD, '2013-01-01', '>='), null),
                )
            ),
            'between_only_end' => array(
                'data' => array(
                    'value' => array(
                        'end' => new \DateTime('2014-01-01'),
                    ),
                    'type' => DateRangeFilterType::TYPE_BETWEEN
                ),
                'expectRepositoryCalls' => array(
                    array('applyFilterByAttribute', array(self::TEST_FIELD, '2014-01-01', '<='), null),
                )
            ),
            'not_between' => array(
                'data' => array(
                    'value' => array(
                        'start' => new \DateTime('2013-01-01'),
                        'end' => new \DateTime('2014-01-01'),
                    ),
                    'type' => DateRangeFilterType::TYPE_NOT_BETWEEN
                ),
                'expectRepositoryCalls' => array(
                    array(
                        'applyFilterByAttribute',
                        array(
                            self::TEST_FIELD,
                            array('from' => '2013-01-01', 'to' => '2014-01-01'),
                            array('from' => '<', 'to' => '>')
                        ),
                        null
                    ),
                )
            ),
        );
    }
}
