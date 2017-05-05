<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\AbstractBridgeSoapIterator;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class BaseIteratorTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var AbstractBridgeSoapIterator */
    protected $iterator;

    /** @var \PHPUnit_Framework_MockObject_MockObject|SoapTransport */
    protected $transport;

    /** @var array */
    protected $settings;

    protected function setUp()
    {
        $this->transport = $this->getMockBuilder('Oro\\Bundle\\MagentoBundle\\Provider\\Transport\\SoapTransport')
            ->disableOriginalConstructor()
            ->getMock();

        $this->settings  = ['start_sync_date' => new \DateTime('NOW'), 'website_id' => 0];
    }

    public function tearDown()
    {
        unset($this->iterator, $this->transport);
    }
}
