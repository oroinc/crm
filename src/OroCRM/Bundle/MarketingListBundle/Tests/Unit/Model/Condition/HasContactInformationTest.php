<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Model\Condition;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Component\ConfigExpression\ContextAccessor;

use OroCRM\Bundle\MarketingListBundle\Model\Condition\HasContactInformation;

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
        $this->condition = new HasContactInformation($this->fieldsProvider);
        $this->condition->setContextAccessor($this->contextAccessor);
    }

    /**
     * @expectedException \Oro\Component\ConfigExpression\Exception\InvalidArgumentException
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
     * @expectedException \Oro\Component\ConfigExpression\Exception\InvalidArgumentException
     */
    public function testEvaluateException()
    {
        $context = [];
        $this->condition->evaluate($context);
    }

    public function testEvaluate()
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
        $this->assertTrue($this->condition->evaluate($context));
    }
}
