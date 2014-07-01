<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Provider;

use OroCRM\Bundle\ChannelBundle\Provider\EntityExclusionProvider;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class EntityExclusionProviderTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ENTITY_NAME = 'TestBundle\Entity\Test';
    const TEST_ASSOC_NAME  = 'relation';
    const TEST_FIELD_NAME  = 'field';

    /** @var EntityExclusionProvider */
    protected $exclusionProvider;

    /** @var SettingsProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $settingsProvider;

    public function setUp()
    {
        $this->settingsProvider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();

        $this->exclusionProvider = new EntityExclusionProvider($this->settingsProvider);
    }

    public function tearDown()
    {
        unset($this->exclusionProvider, $this->settingsProvider);
    }

    /**
     * @dataProvider exclusionProvider
     *
     * @param bool $expected
     * @param bool $isChannelEntity
     * @param bool $isDependentOnChannelEntity
     * @param bool $enabledInChannel
     */
    public function testIsEntityExcluded($expected, $isChannelEntity, $isDependentOnChannelEntity, $enabledInChannel)
    {
        $this->settingsProvider->expects($this->any())
            ->method('isChannelEntity')->with($this->equalTo(self::TEST_ENTITY_NAME))
            ->will($this->returnValue($isChannelEntity));
        $this->settingsProvider->expects($this->any())
            ->method('isDependentOnChannelEntity')->with($this->equalTo(self::TEST_ENTITY_NAME))
            ->will($this->returnValue($isDependentOnChannelEntity));

        $this->assertSame($expected, $this->exclusionProvider->isIgnoredEntity(self::TEST_ENTITY_NAME));
    }

    /**
     * @dataProvider exclusionProvider
     *
     * @param bool $expected
     * @param bool $isChannelEntity
     * @param bool $isDependentOnChannelEntity
     * @param bool $enabledInChannel
     */
    public function testIsRelationExcluded($expected, $isChannelEntity, $isDependentOnChannelEntity, $enabledInChannel)
    {
        $this->settingsProvider->expects($this->any())
            ->method('isChannelEntity')->with($this->equalTo(self::TEST_ENTITY_NAME))
            ->will($this->returnValue($isChannelEntity));
        $this->settingsProvider->expects($this->any())
            ->method('isDependentOnChannelEntity')->with($this->equalTo(self::TEST_ENTITY_NAME))
            ->will($this->returnValue($isDependentOnChannelEntity));

        $classMetadataMock = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()->getMock();
        $classMetadataMock->expects($this->once())
            ->method('getAssociationTargetClass')->with($this->equalTo(self::TEST_ASSOC_NAME))
            ->will($this->returnValue(self::TEST_ENTITY_NAME));

        $this->assertSame(
            $expected,
            $this->exclusionProvider->isIgnoredRelation($classMetadataMock, self::TEST_ASSOC_NAME)
        );
    }

    public function testIsFieldExcluded()
    {
        $classMetadataMock = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()->getMock();

        $this->assertFalse(
            $this->exclusionProvider->isIgnoredField($classMetadataMock, self::TEST_FIELD_NAME),
            'should not exclude any fields'
        );
    }

    /**
     * @return array
     */
    public function exclusionProvider()
    {
        return [
            'not related to channel entity given, should not be skipped' => [false, false, false, null]
        ];
    }
}
