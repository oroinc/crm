<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Provider;

use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationExclusionProvider;

class ContactInformationExclusionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactInformationExclusionProvider
     */
    protected $provider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadata;

    protected function setUp()
    {
        $this->registry       = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->configProvider = $this
            ->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadata       = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new ContactInformationExclusionProvider(
            $this->configProvider,
            $this->registry
        );
    }

    /**
     * @dataProvider entityProvider
     */
    public function testIsIgnoredEntity(
        $className,
        $hasEntityConfig,
        array $fieldNames,
        array $fieldNamesMapping,
        $expected
    ) {
        $config = $this->getMock('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface');
        $this->configProvider
            ->expects($this->at(0))
            ->method('getConfig')
            ->with($this->equalTo($className))
            ->will($this->returnValue($config));

        $config
            ->expects($this->at(0))
            ->method('has')
            ->with($this->equalTo('contact_information'))
            ->will($this->returnValue($hasEntityConfig));

        if (!$hasEntityConfig) {
            $om = $this
                ->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
                ->disableOriginalConstructor()
                ->getMock();

            $om
                ->expects($this->once())
                ->method('getClassMetadata')
                ->with($this->equalTo($className))
                ->will($this->returnValue($this->metadata));

            $this->metadata
                ->expects($this->once())
                ->method('getFieldNames')
                ->will($this->returnValue($fieldNames));

            if (!empty($fieldNames)) {
                $i = 1;
                foreach ($fieldNames as $fieldName) {
                    $this->configProvider
                        ->expects($this->at($i))
                        ->method('getConfig')
                        ->with($this->equalTo($className), $this->equalTo($fieldName))
                        ->will($this->returnValue($config));

                    $config
                        ->expects($this->at($i))
                        ->method('has')
                        ->with($this->equalTo('contact_information'))
                        ->will($this->returnValue($fieldNamesMapping[$fieldName]));

                    $i++;
                }

            }

            $this->registry
                ->expects($this->once())
                ->method('getManagerForClass')
                ->with($this->equalTo($className))
                ->will($this->returnValue($om));
        }

        $result = $this->provider->isIgnoredEntity($className);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function entityProvider()
    {
        return [
            'entity_level'         => ['\stdClass', true, [], [], true],
            'empty_fields'         => ['\stdClass', false, [], [], false],
            'has_not_field_config' => [
                '\stdClass',
                false,
                ['fieldName'],
                ['fieldName' => false],
                false
            ],
            'has_field_config'     => [
                '\stdClass',
                false,
                ['fieldName', 'fieldName2'],
                ['fieldName' => false, 'fieldName2' => true],
                true
            ],
        ];
    }

    public function testIsIgnoredField()
    {
        $this->assertFalse($this->provider->isIgnoredField($this->metadata, 'fieldName'));
    }

    public function testIsIgnoredRelation()
    {
        $this->assertFalse($this->provider->isIgnoredRelation($this->metadata, 'associationName'));
    }
}
