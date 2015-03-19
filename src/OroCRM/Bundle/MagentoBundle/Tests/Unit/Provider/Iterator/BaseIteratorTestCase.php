<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\AbstractBridgeIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class BaseIteratorTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var AbstractBridgeIterator */
    protected $iterator;

    /** @var \PHPUnit_Framework_MockObject_MockObject|SoapTransport */
    protected $transport;

    /** @var array */
    protected $settings;

    protected function setUp()
    {
        $this->transport = $this->getMockBuilder('OroCRM\\Bundle\\MagentoBundle\\Provider\\Transport\\SoapTransport')
            ->disableOriginalConstructor()
            ->getMock();

        $this->settings  = ['start_sync_date' => new \DateTime('NOW'), 'website_id' => 0];
    }

    public function tearDown()
    {
        unset($this->iterator, $this->transport);
    }
}
