<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use OroCRM\Bundle\MarketingListBundle\Model\Condition\HasContactInformation;
use Symfony\Component\PropertyAccess\PropertyPath;

class HasContactInformationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextAccessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldsProvider;

    /**
     * @var HasContactInformation
     */
    protected $condition;

    protected function setUp()
    {
        $this->contextAccessor = new ContextAccessor();
        $this->fieldsProvider = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->condition = new HasContactInformation($this->contextAccessor, $this->fieldsProvider);
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\ConditionException
     * @expectedExceptionMessage Option "marketing_list" is required
     * @dataProvider invalidOptionsDataProvider
     * @param array $options
     */
    public function testInitializeException(array $options)
    {
        $this->condition->initialize($options);
    }

    /**
     * @return array
     */
    public function invalidOptionsDataProvider()
    {
        return [
            'no options' => [[]],
            'no marketing list option' => [['type' => 'test']]
        ];
    }

    /**
     * @dataProvider optionsDataProvider
     * @param array $options
     * @param mixed $expectedList
     * @param mixed $expectedType
     */
    public function testInitialize($options, $expectedList, $expectedType)
    {
        $this->assertSame($this->condition, $this->condition->initialize($options));
        $this->assertAttributeEquals($expectedList, 'marketingList', $this->condition);
        $this->assertAttributeEquals($expectedType, 'type', $this->condition);
    }

    /**
     * @return array
     */
    public function optionsDataProvider()
    {
        return [
            'named' => [
                [
                    'marketing_list' => 'ML',
                    'type' => 'type'
                ],
                'ML',
                'type'
            ],
            'indexed' => [
                [
                    'ML',
                    'type'
                ],
                'ML',
                'type'
            ],
        ];
    }

    /**
     * @expectedException \Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException
     */
    public function testIsAllowedException()
    {
        $context = [];
        $this->condition->isAllowed($context);
    }

    public function testIsAllowed()
    {
        $type = 'test';
        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();
        $context = new \stdClass();
        $context->marketingList = $marketingList;
        $context->type = $type;

        $this->fieldsProvider->expects($this->once())
            ->method('getMarketingListTypedFields')
            ->with($marketingList, $type)
            ->will($this->returnValue(true));

        $options = [
            'marketing_list' => new PropertyPath('marketingList'),
            'type' => new PropertyPath('type')
        ];

        $this->condition->initialize($options);
        $this->assertTrue($this->condition->isAllowed($context));
    }
}
