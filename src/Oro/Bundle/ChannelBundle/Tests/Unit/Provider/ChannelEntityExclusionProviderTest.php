<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Provider;

use Doctrine\ORM\Mapping\ClassMetadata;
use Oro\Bundle\ChannelBundle\Provider\ChannelEntityExclusionProvider;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;

class ChannelEntityExclusionProviderTest extends \PHPUnit\Framework\TestCase
{
    private const TEST_ENTITY_NAME = 'TestBundle\Entity\Test';
    private const TEST_ASSOC_NAME = 'relation';
    private const TEST_FIELD_NAME = 'field';

    /** @var ChannelEntityExclusionProvider */
    private $exclusionProvider;

    /** @var SettingsProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $settingsProvider;

    protected function setUp(): void
    {
        $this->settingsProvider = $this->createMock(SettingsProvider::class);

        $this->exclusionProvider = new ChannelEntityExclusionProvider($this->settingsProvider);
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
            ->method('isChannelEntity')
            ->with(self::TEST_ENTITY_NAME)
            ->willReturn($isChannelEntity);

        $this->assertSame($expected, $this->exclusionProvider->isIgnoredEntity(self::TEST_ENTITY_NAME));
    }

    public function testIsRelationExcluded()
    {
        $classMetadataMock = $this->createMock(ClassMetadata::class);

        $this->assertFalse(
            $this->exclusionProvider->isIgnoredRelation($classMetadataMock, self::TEST_ASSOC_NAME),
            'should not exclude any relation'
        );
    }

    public function testIsFieldExcluded()
    {
        $classMetadataMock = $this->createMock(ClassMetadata::class);

        $this->assertFalse(
            $this->exclusionProvider->isIgnoredField($classMetadataMock, self::TEST_FIELD_NAME),
            'should not exclude any fields'
        );
    }

    public function exclusionProvider(): array
    {
        return [
            'not related to channel entity given, should be skipped'                          => [true, false, null],
            'related to channel entity given, should not skip entities belong to integration' => [true, false, true],
            'related to channel entity given, should not skip'                                => [false, true, false],
        ];
    }
}
