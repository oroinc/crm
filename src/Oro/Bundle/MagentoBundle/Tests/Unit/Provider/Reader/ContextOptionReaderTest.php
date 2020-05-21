<?php
namespace Oro\Bundle\MagentoBundle\Tests\Unit\Importexport\Reader;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\MagentoBundle\Provider\Reader\ContextOptionReader;

class ContextOptionReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ContextOptionReader
     */
    protected $reader;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ContextRegistry
     */
    protected $contextRegistry;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|StepExecution
     */
    protected $stepExecution;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ContextInterface
     */
    protected $context;

    protected function setUp(): void
    {
        $this->contextRegistry = $this->getMockbuilder('Oro\Bundle\ImportExportBundle\Context\ContextRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $this->createMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
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

    public function testReadFailed()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Context key is missing');

        $this->reader->setStepExecution($this->stepExecution);
    }
}
