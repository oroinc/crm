<?php
namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Reader;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use OroCRM\Bundle\MagentoBundle\Provider\Reader\ContextOptionReader;

class ContextOptionReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextOptionReader
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

    protected function setUp()
    {
        $this->contextRegistry = $this->getMockbuilder('Oro\Bundle\ImportExportBundle\Context\ContextRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $this->contextRegistry->expects($this->any())
            ->method('getByStepExecution')
            ->will($this->returnValue($this->context));
        $this->stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();
        $this->reader = new ContextOptionReader($this->contextRegistry);
    }

    public function testReadSame()
    {
        $expected = new \stdClass();
        $expected->prop = 'value';
        $this->context->expects($this->once())
            ->method('getOption')
            ->with($this->equalTo('entity'))
            ->will($this->returnValue($expected));

        $this->reader->setContextKey('entity');
        $this->reader->setStepExecution($this->stepExecution);
        $this->assertEquals($expected, $this->reader->read());
        $this->assertNull($this->reader->read());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Context key is missing
     */
    public function testReadFailed()
    {
        $this->reader->setStepExecution($this->stepExecution);
    }
}
