<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Oro\Bundle\ImportExportBundle\Job\JobResult;

use OroCRM\Bundle\MagentoBundle\EventListener\AfterJobExecutionEventListener;

class AfterJobExecutionEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var AfterJobExecutionEventListener */
    protected $listener;

    public function setUp()
    {
        $this->listener = new AfterJobExecutionEventListener();
    }

    public function testProcess()
    {
        $message = '<?xml version="1.0" encoding="UTF-8"?>' .
            '<SOAP-ENV:Body><ns1:login><username xsi:type="xsd:string">abc</username>' .
            '<apiKey xsi:type="xsd:string">abcabc1</apiKey></ns1:login></SOAP-ENV:Body></SOAP-ENV:Envelope>';

        $validMessage = '<?xml version="1.0" encoding="UTF-8"?>' .
            '<SOAP-ENV:Body><ns1:login><username xsi:type="xsd:string">abc</username>' .
            '</ns1:login></SOAP-ENV:Body></SOAP-ENV:Envelope>';

        $jobResult = new JobResult();
        $jobResult->addFailureException($message);

        $event = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Event\AfterJobExecutionEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getJobResult')
            ->will($this->returnValue($jobResult));

        $this->listener->process($event);

        foreach ($jobResult->getFailureExceptions() as $exeption) {
            $this->assertEquals($exeption, $validMessage);
        }
    }
}
