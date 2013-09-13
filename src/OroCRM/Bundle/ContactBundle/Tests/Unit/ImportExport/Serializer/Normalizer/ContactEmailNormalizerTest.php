<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactEmailNormalizer;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;

class ContactPhoneNormalizerTest extends \PHPUnit_Framework_TestCase
{
    const CONTACT_EMAIL_TYPE = 'OroCRM\Bundle\ContactBundle\Entity\ContactEmail';

    /**
     * @var ContactEmailNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->normalizer = new ContactEmailNormalizer();
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(array()));
        $this->assertTrue($this->normalizer->supportsNormalization($this->createContactPhone()));
    }

    public function testSupportsDenormalization()
    {
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), 'stdClass'));
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), self::CONTACT_EMAIL_TYPE));
        $this->assertTrue($this->normalizer->supportsDenormalization('email@example.com', self::CONTACT_EMAIL_TYPE));
    }

    public function testNormalize()
    {
        $this->assertEquals(
            'email@example.com',
            $this->normalizer->normalize($this->createContactPhone()->setEmail('email@example.com'), null)
        );
    }

    public function testDenormalize()
    {
        $this->assertEquals(
            $this->createContactPhone()->setEmail('email@example.com'),
            $this->normalizer->denormalize('email@example.com', null)
        );
    }

    protected function createContactPhone()
    {
        return new ContactEmail();
    }
}
