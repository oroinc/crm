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
        $this->registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->configProvider = $this
            ->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadata = $this
            ->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new ContactInformationExclusionProvider(
            $this->configProvider,
            $this->registry
        );
    }

    public function testIsIgnoredEntityHasEntityLevel()
    {
        $className = 'stdClass';

        $this->configProvider->expects($this->once())
            ->method('hasConfig')
            ->with($className)
            ->will($this->returnValue(true));

        $config = $this->getMock('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface');

        $config
            ->expects($this->once())
            ->method('has')
            ->with($this->equalTo('contact_information'))
            ->will($this->returnValue(true));

        $this->configProvider
            ->expects($this->once())
            ->method('getConfig')
            ->with($this->equalTo($className))
            ->will($this->returnValue($config));

        $this->assertFalse($this->provider->isIgnoredEntity($className));
    }

    public function testIsIgnoredEntityHasContactInformationField()
    {
        $className = 'stdClass';
        $field = 'field1';

        $this->configProvider->expects($this->atLeastOnce())
            ->method('hasConfig')
            ->will(
                $this->returnValueMap(
                    [
                        [$className, null, true],
                        [$className, $field, true],
                    ]
                )
            );

        $entityConfig = $this->getMock('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface');
        $entityConfig->expects($this->once())
            ->method('has')
            ->with($this->equalTo('contact_information'))
            ->will($this->returnValue(false));

        $fieldConfig = $this->getMock('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface');
        $fieldConfig->expects($this->once())
            ->method('has')
            ->with($this->equalTo('contact_information'))
            ->will($this->returnValue(true));

        $this->configProvider->expects($this->atLeastOnce())
            ->method('getConfig')
            ->will(
                $this->returnValueMap(
                    [
                        [$className, null, $entityConfig],
                        [$className, $field, $fieldConfig]
                    ]
                )
            );

        $om = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $om->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo($className))
            ->will($this->returnValue($this->metadata));

        $this->metadata
            ->expects($this->once())
            ->method('getFieldNames')
            ->will($this->returnValue([$field]));

        $this->registry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with($this->equalTo($className))
            ->will($this->returnValue($om));

        $this->assertFalse($this->provider->isIgnoredEntity($className));
    }

    public function testIsIgnoredEntityHasNoContactInformationFields()
    {
        $className = 'stdClass';
        $field = 'field1';

        $this->configProvider->expects($this->atLeastOnce())
            ->method('hasConfig')
            ->will(
                $this->returnValueMap(
                    [
                        [$className, null, true],
                        [$className, $field, true],
                    ]
                )
            );

        $entityConfig = $this->getMock('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface');
        $entityConfig->expects($this->once())
            ->method('has')
            ->with($this->equalTo('contact_information'))
            ->will($this->returnValue(false));

        $fieldConfig = $this->getMock('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface');
        $fieldConfig->expects($this->once())
            ->method('has')
            ->with($this->equalTo('contact_information'))
            ->will($this->returnValue(false));

        $this->configProvider->expects($this->atLeastOnce())
            ->method('getConfig')
            ->will(
                $this->returnValueMap(
                    [
                        [$className, null, $entityConfig],
                        [$className, $field, $fieldConfig]
                    ]
                )
            );

        $om = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $om->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo($className))
            ->will($this->returnValue($this->metadata));

        $this->metadata
            ->expects($this->once())
            ->method('getFieldNames')
            ->will($this->returnValue([$field]));

        $this->registry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with($this->equalTo($className))
            ->will($this->returnValue($om));

        $this->assertTrue($this->provider->isIgnoredEntity($className));
    }

    public function testIsIgnoredEntityHasNoConfigurableFields()
    {
        $className = 'stdClass';
        $field = 'field1';

        $this->configProvider->expects($this->atLeastOnce())
            ->method('hasConfig')
            ->will(
                $this->returnValueMap(
                    [
                        [$className, null, true],
                        [$className, $field, false],
                    ]
                )
            );

        $entityConfig = $this->getMock('Oro\Bundle\EntityConfigBundle\Config\ConfigInterface');
        $entityConfig->expects($this->once())
            ->method('has')
            ->with($this->equalTo('contact_information'))
            ->will($this->returnValue(false));

        $this->configProvider->expects($this->once())
            ->method('getConfig')
            ->with($className)
            ->will($this->returnValue($entityConfig));

        $om = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $om->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo($className))
            ->will($this->returnValue($this->metadata));

        $this->metadata
            ->expects($this->once())
            ->method('getFieldNames')
            ->will($this->returnValue([$field]));

        $this->registry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with($this->equalTo($className))
            ->will($this->returnValue($om));

        $this->assertTrue($this->provider->isIgnoredEntity($className));
    }

    public function testNonConfigurableIgnored()
    {
        $class = 'stdClass';

        $this->configProvider->expects($this->once())
            ->method('hasConfig')
            ->with($class)
            ->will($this->returnValue(false));

        $this->assertTrue($this->provider->isIgnoredEntity($class));
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
