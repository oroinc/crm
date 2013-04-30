<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\Provider\ImportExport;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Provider\ImportExport;
use Doctrine\Common\Persistence\ObjectManager;

class IntlReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ReaderInterface
     */
    private $reader;

    /**
     * @var string
     */
    private $class;

    /**
     * @var int
     */
    private $batchSize = 200;

    /**
     * Environment setup
     */
    public function setUp()
    {
        $this->class = 'Oro\Bundle\AddressBundle\Entity\Country';

        $this->reader = new ImportExport\IntlReader($this->class, $this->batchSize);
    }

    /**
     * Test setting limits
     */
    public function testSettingBatchLimit()
    {
        $this->assertEquals($this->batchSize, $this->reader->getBatchSize());
    }

    /**
     * Test read batch
     */
    public function testReader()
    {
        $data = $this->reader->readBatch();
        $this->assertTrue(count($data) == $this->batchSize);

        $this->reader->reset();
        $data2 = $this->reader->readBatch();

        $this->assertEquals($data, $data2);

        $this->reader->readBatch();
        $this->assertFalse($this->reader->readBatch());
    }
}
