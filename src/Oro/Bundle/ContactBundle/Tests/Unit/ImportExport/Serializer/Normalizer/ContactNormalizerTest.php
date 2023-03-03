<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Formatter\SocialUrlFormatter;
use Oro\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactNormalizer;
use Oro\Bundle\ContactBundle\Model\Social;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\ImportExportBundle\Tests\Unit\Strategy\Stub\ImportEntity;

class ContactNormalizerTest extends \PHPUnit\Framework\TestCase
{
    /** @var SocialUrlFormatter|\PHPUnit\Framework\MockObject\MockObject */
    private $socialUrlFormatter;

    /** @var FieldHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $fieldHelper;

    /** @var ContactNormalizer */
    private $normalizer;

    protected function setUp(): void
    {
        $this->socialUrlFormatter = $this->createMock(SocialUrlFormatter::class);
        $this->fieldHelper = $this->createMock(FieldHelper::class);

        $this->normalizer = new ContactNormalizer(
            $this->fieldHelper
        );
    }

    /**
     * @dataProvider socialDataProvider
     */
    public function testNormalize(string $data, string $link): void
    {
        $object = new ImportEntity();
        $object->setTwitter($data);

        $this->fieldHelper->expects(self::once())
            ->method('getEntityFields')
            ->willReturn(
                [
                    ['name' => 'twitter']
                ]
            );
        $this->fieldHelper->expects(self::any())
            ->method('getObjectValue')
            ->willReturnCallback(function ($object, $field) {
                return PropertyAccess::createPropertyAccessor()->getValue($object, $field);
            });

        $this->socialUrlFormatter->expects(self::once())
            ->method('getSocialUrl')
            ->with(Social::TWITTER, $data)
            ->willReturn($link);

        $this->normalizer->setSocialUrlFormatter($this->socialUrlFormatter);
        $result = $this->normalizer->normalize($object);

        self::assertEquals([Social::TWITTER => $link], $result);
    }

    /**
     * @dataProvider socialDataProvider
     */
    public function testDenormalize(string $data, string $link): void
    {
        $this->fieldHelper->expects(self::once())
            ->method('getEntityFields')
            ->willReturn(
                [
                    ['name' => 'twitter']
                ]
            );

        $this->socialUrlFormatter->expects(self::once())
            ->method('getSocialUsername')
            ->with(Social::TWITTER, $data)
            ->willReturn($link);

        $this->fieldHelper->expects(self::once())
            ->method('setObjectValue')
            ->with(new ImportEntity(), Social::TWITTER, $link);

        $this->normalizer->setSocialUrlFormatter($this->socialUrlFormatter);

        /** @var ImportEntity $result */
        $this->normalizer->denormalize(
            [Social::TWITTER => $data],
            ImportEntity::class
        );
    }

    public function socialDataProvider(): array
    {
        return [
            'username' => [
                'twitter-username',
                'https://twitter.com/twitter-username'
            ],
            'link'     => [
                'https://twitter.com/twitter-username-link',
                'https://twitter.com/twitter-username-link'
            ]
        ];
    }

    public function testSupportsNormalization(): void
    {
        self::assertTrue($this->normalizer->supportsNormalization(new Contact()));
        self::assertFalse($this->normalizer->supportsNormalization(new ImportEntity()));
    }

    /**
     * @dataProvider denormalizationProvider
     */
    public function testSupportsDenormalization(?array $data, string $type, string $method): void
    {
        $this->{'assert' . $method}($this->normalizer->supportsDenormalization($data, $type));
    }

    public function denormalizationProvider(): array
    {
        return [
            'empty'   => [null, '', 'false'],
            'array'   => [[], '', 'false'],
            'type'    => [null, ContactNormalizer::CONTACT_TYPE, 'false'],
            'support' => [[], ContactNormalizer::CONTACT_TYPE, 'true']
        ];
    }
}
