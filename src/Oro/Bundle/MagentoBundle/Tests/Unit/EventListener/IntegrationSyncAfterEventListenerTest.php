<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Oro\Bundle\ImportExportBundle\Job\JobResult;
use Oro\Bundle\IntegrationBundle\Event\SyncEvent;
use Oro\Bundle\MagentoBundle\EventListener\IntegrationSyncAfterEventListener;

class IntegrationSyncAfterEventListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var IntegrationSyncAfterEventListener */
    protected $listener;

    protected function setUp(): void
    {
        $this->listener = new IntegrationSyncAfterEventListener();
    }

    protected function tearDown(): void
    {
        unset($this->listener);
    }

    /**
     * @dataProvider eventDataProvider
     *
     * @param string $jobName
     * @param string $exceptionMessage
     * @param string $expectedMessage
     */
    public function testProcess($jobName, $exceptionMessage, $expectedMessage)
    {
        $jobResult = new JobResult();
        $jobResult->addFailureException($exceptionMessage);

        $event = new SyncEvent($jobName, [], $jobResult);
        $this->listener->process($event);

        $exceptions = $jobResult->getFailureExceptions();
        $this->assertEquals($expectedMessage, reset($exceptions));
    }

    /**
     * @return array
     */
    public function eventDataProvider()
    {
        return [
            'not magento bundle related job should be skipped' => [
                '$jobName'          => 'some_job_name',
                '$exceptionMessage' => '<?xml version="1.0" encoding="UTF-8"?>' .
                    '<SOAP-ENV:Body><ns1:login><username xsi:type="xsd:string">abc</username>' .
                    '<apiKey xsi:type="xsd:string">abcabc1</apiKey></ns1:login></SOAP-ENV:Body></SOAP-ENV:Envelope>',
                '$expectedMessage'  => '<?xml version="1.0" encoding="UTF-8"?>' .
                    '<SOAP-ENV:Body><ns1:login><username xsi:type="xsd:string">abc</username>' .
                    '<apiKey xsi:type="xsd:string">abcabc1</apiKey></ns1:login></SOAP-ENV:Body></SOAP-ENV:Envelope>'
            ],
            ' magento bundle related job should process'       => [
                '$jobName'          => 'mage_customer',
                '$exceptionMessage' => '<?xml version="1.0" encoding="UTF-8"?>' .
                    '<SOAP-ENV:Body><ns1:login><username xsi:type="xsd:string">abc</username>' .
                    '<apiKey xsi:type="xsd:string">abcabc1</apiKey></ns1:login></SOAP-ENV:Body></SOAP-ENV:Envelope>',
                '$expectedMessage'  => '<?xml version="1.0" encoding="UTF-8"?>' .
                    '<SOAP-ENV:Body><ns1:login><username xsi:type="xsd:string">abc</username>' .
                    '<apiKey xsi:type="xsd:string">***</apiKey></ns1:login></SOAP-ENV:Body></SOAP-ENV:Envelope>'
            ]
        ];
    }
}
