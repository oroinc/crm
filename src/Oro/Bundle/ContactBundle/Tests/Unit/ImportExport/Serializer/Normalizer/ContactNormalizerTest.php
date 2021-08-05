<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Formatter\SocialUrlFormatter;
use Oro\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactNormalizer;
use Oro\Bundle\ContactBundle\Model\Social;
use Oro\Bundle\EntityBundle\Helper\FieldHelper;
use Oro\Bundle\ImportExportBundle\Tests\Unit\Strategy\Stub\ImportEntity;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ContactNormalizerTest extends \PHPUnit\Framework\TestCase
{
    private SocialUrlFormatter|\PHPUnit\Framework\MockObject\MockObject $socialUrlFormatter;

    private FieldHelper|\PHPUnit\Framework\MockObject\MockObject $fieldHelper;

    private ContactNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->socialUrlFormatter = $this
            ->getMockBuilder(SocialUrlFormatter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fieldHelper = $this
            ->getMockBuilder(FieldHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->normalizer = new ContactNormalizer(
            $this->fieldHelper
        );
    }

    /**
     * @param string $data
     * @param string $link
     *
     * @dataProvider socialDataProvider
     */
    public function testNormalize($data, $link): void
    {
        $object = new ImportEntity();
        $object->setTwitter($data);

        $this->fieldHelper
            ->expects(self::once())
            ->method('getEntityFields')
            ->willReturn(
                [
                    ['name' => 'twitter']
                ]
            );
        $this->fieldHelper->expects(self::any())
            ->method('getObjectValue')
            ->willReturnCallback(
                function ($object, $field) {
                    $propertyAccessor = PropertyAccess::createPropertyAccessor();

                    return $propertyAccessor->getValue($object, $field);
                }
            );

        $this->socialUrlFormatter
            ->expects(self::once())
            ->method('getSocialUrl')
            ->with(self::equalTo(Social::TWITTER), self::equalTo($data))
            ->willReturn($link);

        $this->normalizer->setSocialUrlFormatter($this->socialUrlFormatter);
        $result = $this->normalizer->normalize($object);

        self::assertEquals([Social::TWITTER => $link], $result);
    }

    /**
     * @param string $data
     * @param string $link
     *
     * @dataProvider socialDataProvider
     */
    public function testDenormalize($data, $link): void
    {
        $this->fieldHelper
            ->expects(self::once())
            ->method('getEntityFields')
            ->willReturn(
                [
                    ['name' => 'twitter']
                ]
            );

        $this->socialUrlFormatter
            ->expects(self::once())
            ->method('getSocialUsername')
            ->with(self::equalTo(Social::TWITTER), self::equalTo($data))
            ->willReturn($link);

        $this->fieldHelper
            ->expects(self::once())
            ->method('setObjectValue')
            ->with(
                self::equalTo(new ImportEntity()),
                self::equalTo(Social::TWITTER),
                self::equalTo($link)
            );

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
     * @param mixed  $data
     * @param string $type
     * @param string $method
     *
     * @dataProvider denormalizationProvider
     */
    public function testSupportsDenormalization($data, $type, $method): void
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
