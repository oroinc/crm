<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\ImportExportBundle\Tests\Unit\Strategy\Stub\ImportEntity;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactNormalizer;
use OroCRM\Bundle\ContactBundle\Model\Social;

class ContactNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $socialUrlFormatter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldHelper;

    /**
     * @var ContactNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->socialUrlFormatter = $this
            ->getMockBuilder('OroCRM\Bundle\ContactBundle\Formatter\SocialUrlFormatter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->fieldHelper = $this
            ->getMockBuilder('Oro\Bundle\ImportExportBundle\Field\FieldHelper')
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
    public function testNormalize($data, $link)
    {
        $object = new ImportEntity();
        $object->setTwitter($data);

        $this->fieldHelper
            ->expects($this->once())
            ->method('getFields')
            ->will(
                $this->returnValue(
                    [
                        ['name' => 'twitter']
                    ]
                )
            );
        $this->fieldHelper->expects($this->any())
            ->method('getObjectValue')
            ->will(
                $this->returnCallback(
                    function ($object, $field) {
                        $propertyAccessor = PropertyAccess::createPropertyAccessor();
                        return $propertyAccessor->getValue($object, $field);
                    }
                )
            );

        $this->socialUrlFormatter
            ->expects($this->once())
            ->method('getSocialUrl')
            ->with($this->equalTo(Social::TWITTER), $this->equalTo($data))
            ->will($this->returnValue($link));

        $this->normalizer->setSocialUrlFormatter($this->socialUrlFormatter);
        $result = $this->normalizer->normalize($object);

        $this->assertEquals([Social::TWITTER => $link], $result);
    }

    /**
     * @param string $data
     * @param string $link
     *
     * @dataProvider socialDataProvider
     */
    public function testDenormalize($data, $link)
    {
        $this->fieldHelper
            ->expects($this->once())
            ->method('getFields')
            ->will(
                $this->returnValue(
                    [
                        ['name' => 'twitter']
                    ]
                )
            );

        $this->socialUrlFormatter
            ->expects($this->once())
            ->method('getSocialUsername')
            ->with($this->equalTo(Social::TWITTER), $this->equalTo($data))
            ->will($this->returnValue($link));

        $this->fieldHelper
            ->expects($this->once())
            ->method('setObjectValue')
            ->with(
                $this->equalTo(new ImportEntity()),
                $this->equalTo(Social::TWITTER),
                $this->equalTo($link)
            );

        $this->normalizer->setSocialUrlFormatter($this->socialUrlFormatter);

        /** @var ImportEntity $result */
        $this->normalizer->denormalize(
            [Social::TWITTER => $data],
            'Oro\Bundle\ImportExportBundle\Tests\Unit\Strategy\Stub\ImportEntity'
        );
    }

    public function socialDataProvider()
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

    public function testSupportsNormalization()
    {
        $this->assertTrue($this->normalizer->supportsNormalization(new Contact()));
        $this->assertFalse($this->normalizer->supportsNormalization(new ImportEntity()));
    }

    /**
     * @param mixed  $data
     * @param string $type
     * @param string $method
     *
     * @dataProvider denormalizationProvider
     */
    public function testSupportsDenormalization($data, $type, $method)
    {
        $this->{'assert' . $method}($this->normalizer->supportsDenormalization($data, $type));
    }

    public function denormalizationProvider()
    {
        return [
            'empty'   => [null, null, 'false'],
            'array'   => [[], null, 'false'],
            'type'    => [null, ContactNormalizer::CONTACT_TYPE, 'false'],
            'support' => [[], ContactNormalizer::CONTACT_TYPE, 'true']
        ];
    }
}
