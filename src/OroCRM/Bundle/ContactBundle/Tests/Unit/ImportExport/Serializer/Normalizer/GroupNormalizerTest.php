<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\GroupNormalizer;
use OroCRM\Bundle\ContactBundle\Entity\Group;

class GroupNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GroupNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->normalizer = new GroupNormalizer();
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(array()));
        $this->assertTrue($this->normalizer->supportsNormalization($this->createGroup('group')));
    }

    public function testSupportsDenormalization()
    {
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), 'stdClass'));
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), GroupNormalizer::GROUP_TYPE));
        $this->assertTrue($this->normalizer->supportsDenormalization('group', GroupNormalizer::GROUP_TYPE));
    }

    public function testNormalize()
    {
        $this->assertEquals(
            'group',
            $this->normalizer->normalize($this->createGroup('group'), null)
        );
    }

    public function testDenormalize()
    {
        $this->assertEquals(
            $this->createGroup('foo'),
            $this->normalizer->denormalize('foo', null)
        );
    }

    protected function createGroup($name)
    {
        return new Group($name);
    }
}
