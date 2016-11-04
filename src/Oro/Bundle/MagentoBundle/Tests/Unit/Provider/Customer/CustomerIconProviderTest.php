<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\Customer\CustomerIconProvider;
use Oro\Bundle\UIBundle\Model\Image;

class CustomerIconProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerIconProvider */
    protected $customerIconProvider;

    public function setUp()
    {
        $channelType = $this->getMock('Oro\Bundle\MagentoBundle\Provider\ChannelType');
        $channelType->expects($this->any())
            ->method('getIcon')
            ->willReturn('bundles/acmedemo/img/logo.png');

        $this->customerIconProvider = new CustomerIconProvider($channelType);
    }

    public function testShouldReturnIconForMagentoCustomer()
    {
        $icon = $this->customerIconProvider->getIcon(new Customer());
        $this->assertEquals(
            new Image(Image::TYPE_FILE_PATH, ['path' => 'bundles/acmedemo/img/logo.png']),
            $icon
        );
    }

    public function testShouldReturnNullForOtherEntities()
    {
        $icon = $this->customerIconProvider->getIcon(new \StdClass());
        $this->assertEquals(
            null,
            $icon
        );
    }
}
