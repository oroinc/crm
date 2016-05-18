<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Provider;

use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;
use OroCRM\Bundle\ChannelBundle\Provider\ChannelEntityExclusionProvider;

class ChannelEntityExclusionProviderTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ENTITY_NAME = 'TestBundle\Entity\Test';
    const TEST_ASSOC_NAME  = 'relation';
    const TEST_FIELD_NAME  = 'field';

    /** @var ChannelEntityExclusionProvider */
    protected $exclusionProvider;

    /** @var SettingsProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $settingsProvider;

    public function setUp()
    {
        $this->settingsProvider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();

        $this->exclusionProvider = new ChannelEntityExclusionProvider($this->settingsProvider);
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
     */
    public function testIsEntityExcluded($expected, $isChannelEntity)
    {
        $this->settingsProvider->expects($this->any())
            ->method('isChannelEntity')->with($this->equalTo(self::TEST_ENTITY_NAME))
            ->will($this->returnValue($isChannelEntity));

        $this->assertSame($expected, $this->exclusionProvider->isIgnoredEntity(self::TEST_ENTITY_NAME));
    }

    public function testIsRelationExcluded()
    {
        $classMetadataMock = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()->getMock();

        $this->assertFalse(
            $this->exclusionProvider->isIgnoredRelation($classMetadataMock, self::TEST_ASSOC_NAME),
            'should not exclude any relation'
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
            'not related to channel entity given, should be skipped'                          => [true, false, null],
            'related to channel entity given, should not skip entities belong to integration' => [true, false, true],
            'related to channel entity given, should not skip'                                => [false, true, false],
        ];
    }
}
