<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\SourceNormalizer;
use OroCRM\Bundle\ContactBundle\Entity\Source;

class SourceNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SourceNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->normalizer = new SourceNormalizer();
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(array()));
        $this->assertTrue($this->normalizer->supportsNormalization($this->createSource('source')));
    }

    public function testSupportsDenormalization()
    {
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), 'stdClass'));
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), SourceNormalizer::SOURCE_TYPE));
        $this->assertTrue($this->normalizer->supportsDenormalization('source', SourceNormalizer::SOURCE_TYPE));
    }

    public function testNormalize()
    {
        $this->assertEquals(
            'source',
            $this->normalizer->normalize($this->createSource('source'), null)
        );
    }

    public function testDenormalize()
    {
        $this->assertEquals(
            $this->createSource('foo'),
            $this->normalizer->denormalize('foo', null)
        );
    }

    protected function createSource($name)
    {
        return new Source($name);
    }
}
