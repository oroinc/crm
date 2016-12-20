<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\Customer\CustomerIconProvider;
use Oro\Bundle\UIBundle\Model\Image;

class CustomerIconProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerIconProvider */
    protected $customerIconProvider;

    /** @var CacheManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $cacheManager;

    public function setUp()
    {
        $channelType = $this->getMock('Oro\Bundle\MagentoBundle\Provider\ChannelType');
        $channelType->expects($this->any())
            ->method('getIcon')
            ->willReturn('bundles/acmedemo/img/logo.png');

        $this->cacheManager = $this
            ->getMockBuilder('Liip\ImagineBundle\Imagine\Cache\CacheManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerIconProvider = new CustomerIconProvider($channelType, $this->cacheManager);
    }

    public function testShouldReturnIconForMagentoCustomer()
    {
        $path = '//http://dev_ce.local/media/cache/avatar_xsmall/bundles/acmedemo/img/logo.png';
        $this->cacheManager
            ->expects($this->once())
            ->method('getBrowserPath')
            ->with('bundles/acmedemo/img/logo.png', 'avatar_xsmall')
            ->willReturn($path);
        $icon = $this->customerIconProvider->getIcon(new Customer());

        $this->assertEquals(
            new Image(Image::TYPE_FILE_PATH, ['path' => $path]),
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
