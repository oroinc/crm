<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactPhoneNormalizer;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;

class ContactPhoneNormalizerTest extends \PHPUnit_Framework_TestCase
{
    const METHOD_TYPE = 'OroCRM\Bundle\ContactBundle\Entity\ContactPhone';

    /**
     * @var ContactPhoneNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->normalizer = new ContactPhoneNormalizer();
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(array()));
        $this->assertTrue($this->normalizer->supportsNormalization($this->createContactPhone()));
    }

    public function testSupportsDenormalization()
    {
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), 'stdClass'));
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), self::METHOD_TYPE));
        $this->assertTrue($this->normalizer->supportsDenormalization('contact_phone', self::METHOD_TYPE));
    }

    public function testNormalize()
    {
        $this->assertEquals(
            'contact_phone',
            $this->normalizer->normalize($this->createContactPhone()->setPhone('contact_phone'), null)
        );
    }

    public function testDenormalize()
    {
        $this->assertEquals(
            $this->createContactPhone()->setPhone('contact_phone'),
            $this->normalizer->denormalize('contact_phone', null)
        );
    }

    protected function createContactPhone()
    {
        return new ContactPhone();
    }
}
