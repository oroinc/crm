<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Placeholder;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Placeholder\PlaceholderFilter;

class PlaceholderFilterTest extends \PHPUnit\Framework\TestCase
{
    /** @var PlaceholderFilter */
    private $filter;

    protected function setUp(): void
    {
        $this->filter = new PlaceholderFilter();
    }

    /**
     * @dataProvider isApplicableProvider
     * @param mixed $entity
     * @param bool $expectedResult
     */
    public function testIsApplicable($entity, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->filter->isApplicable($entity));
    }

    public function isApplicableProvider()
    {
        return [
            'null' => [
                'entity' => null,
                'expectedResult' => false
            ],
            'not Magento Customer entity' => [
                'promotion' => new \stdClass(),
                'expectedResult' => false
            ],
            'Magento Customer entity' => [
                'promotion' => new Customer(),
                'expectedResult' => true
            ]
        ];
    }
}
