<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Reader;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use OroCRM\Bundle\MagentoBundle\ImportExport\Reader\ContextEntityReader;

class ContextEntityReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextEntityReader
     */
    protected $reader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContextRegistry
     */
    protected $contextRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StepExecution
     */
    protected $stepExecution;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContextInterface
     */
    protected $context;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    protected function setUp()
    {
        $this->contextRegistry = $this->getMockbuilder('Oro\Bundle\ImportExportBundle\Context\ContextRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')->getMock();

        $this->contextRegistry->expects($this->any())
            ->method('getByStepExecution')
            ->will($this->returnValue($this->context));

        $this->stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->reader = new ContextEntityReader($this->contextRegistry);
        $this->reader->setStepExecution($this->stepExecution);
        $this->reader->setDoctrineHelper($this->doctrineHelper);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Object expected, "NULL" given
     */
    public function testReadFailed()
    {
        $this->reader->read();
    }

    public function testReadSame()
    {
        $expected = new \stdClass();

        $this->doctrineHelper->expects($this->exactly(2))
            ->method('getSingleEntityIdentifier')
            ->will($this->returnValue(1));

        $this->context->expects($this->exactly(2))
            ->method('getOption')
            ->with($this->equalTo('entity'))
            ->will($this->returnValue($expected));

        $this->assertEquals($expected, $this->reader->read());
        $this->assertNull($this->reader->read());
    }

    public function testReadDifferent()
    {
        $expected = new \stdClass();

        $this->doctrineHelper->expects($this->at(0))
            ->method('getSingleEntityIdentifier')
            ->will($this->returnValue(1));
        $this->doctrineHelper->expects($this->at(1))
            ->method('getSingleEntityIdentifier')
            ->will($this->returnValue(2));

        $this->context->expects($this->exactly(2))
            ->method('getOption')
            ->with($this->equalTo('entity'))
            ->will($this->returnValue($expected));

        $this->assertEquals($expected, $this->reader->read());
        $this->assertEquals($expected, $this->reader->read());
    }
}
