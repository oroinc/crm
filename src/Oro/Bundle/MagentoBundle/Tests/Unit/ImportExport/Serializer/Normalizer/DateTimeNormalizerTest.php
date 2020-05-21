<?php

namespace Oro\Bundle\MagentoBundle\Test\Unit\ImportExport\Serializer\Normalizer;

use Oro\Bundle\MagentoBundle\ImportExport\Serializer\Normalizer\DateTimeNormalizer;

class DateTimeNormalizerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DateTimeNormalizer
     */
    protected $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new DateTimeNormalizer();
    }

    /**
     * @dataProvider denormalizeDataProvider
     * @param string $date
     * @param array $context
     * @param \DateTime $expected
     */
    public function testDenormalize($date, $context, $expected)
    {
        $this->assertEquals($expected, $this->normalizer->denormalize($date, 'DateTime', null, $context));
    }

    public function denormalizeDataProvider()
    {
        return array(
            array(
                '2012-02-03 12:03:10',
                array(),
                new \DateTime('2012-02-03 12:03:10', new \DateTimeZone('UTC'))
            ),
            array(
                '2012-02-03',
                array('type' => 'date'),
                new \DateTime('2012-02-03', new \DateTimeZone('UTC'))
            ),
            array(
                '2014-07-05T08:43:31-07:00',
                array(),
                new \DateTime('2014-07-05T08:43:31-07:00', new \DateTimeZone('UTC'))
            )
        );
    }

    /**
     * @dataProvider normalizeDataProvider
     * @param \DateTime $date
     * @param array $context
     * @param string $expected
     */
    public function testNormalize($date, $context, $expected)
    {
        $this->assertEquals($expected, $this->normalizer->normalize($date, null, $context));
    }

    public function normalizeDataProvider()
    {
        return array(
            array(
                new \DateTime('2012-02-03 12:03:10', new \DateTimeZone('UTC')),
                array(),
                '2012-02-03 12:03:10'
            ),
            array(
                new \DateTime('2012-02-03', new \DateTimeZone('UTC')),
                array('type' => 'date'),
                '2012-02-03',
            ),
        );
    }
}
