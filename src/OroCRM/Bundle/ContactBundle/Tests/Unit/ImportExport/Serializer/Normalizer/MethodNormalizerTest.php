<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\MethodNormalizer;
use OroCRM\Bundle\ContactBundle\Entity\Method;

class MethodNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MethodNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->normalizer = new MethodNormalizer();
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(array()));
        $this->assertTrue($this->normalizer->supportsNormalization($this->createMethod('method')));
    }

    public function testSupportsDenormalization()
    {
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), 'stdClass'));
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), MethodNormalizer::METHOD_TYPE));
        $this->assertTrue($this->normalizer->supportsDenormalization('method', MethodNormalizer::METHOD_TYPE));
    }

    public function testNormalize()
    {
        $this->assertEquals(
            'method',
            $this->normalizer->normalize($this->createMethod('method'), null)
        );
    }

    public function testDenormalize()
    {
        $this->assertEquals(
            $this->createMethod('foo'),
            $this->normalizer->denormalize('foo', null)
        );
    }

    protected function createMethod($name)
    {
        return new Method($name);
    }
}
