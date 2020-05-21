<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\Customer\CustomerIconProvider;
use Oro\Bundle\UIBundle\Model\Image;

class CustomerIconProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerIconProvider */
    protected $customerIconProvider;

    /** @var CacheManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $cacheManager;

    /** @var TypesRegistry|\PHPUnit\Framework\MockObject\MockObject */
    protected $channelTypeRegistry;

    protected function setUp(): void
    {
        $this->channelTypeRegistry = $this->createMock(TypesRegistry::class);

        $this->cacheManager = $this
            ->getMockBuilder('Liip\ImagineBundle\Imagine\Cache\CacheManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->customerIconProvider = new CustomerIconProvider($this->channelTypeRegistry, $this->cacheManager);
    }

    public function testShouldReturnIconForMagentoCustomer()
    {
        $integrationTypeName = 'integration_type';

        $channelType = $this->createMock(IconAwareIntegrationInterface::class);
        $channelType->expects($this->any())
            ->method('getIcon')
            ->willReturn('bundles/acmedemo/img/logo.png');

        $this->channelTypeRegistry
            ->expects($this->atLeastOnce())
            ->method('getIntegrationByType')
            ->with($integrationTypeName)
            ->willReturn($channelType);

        $path = '//http://dev_ce.local/media/cache/avatar_xsmall/bundles/acmedemo/img/logo.png';
        $this->cacheManager
            ->expects($this->once())
            ->method('getBrowserPath')
            ->with('bundles/acmedemo/img/logo.png', 'avatar_xsmall')
            ->willReturn($path);

        $channel = new Channel();
        $channel->setType($integrationTypeName);
        $customer = new Customer();
        $customer->setChannel($channel);

        $icon = $this->customerIconProvider->getIcon($customer);

        $this->assertEquals(
            new Image(Image::TYPE_FILE_PATH, ['path' => $path]),
            $icon
        );
    }

    public function testShouldntReturnIconForMagentoCustomer()
    {
        $integrationTypeName = 'integration_type';

        $channelType = $this->createMock(ChannelInterface::class);

        $this->channelTypeRegistry
            ->expects($this->atLeastOnce())
            ->method('getIntegrationByType')
            ->with($integrationTypeName)
            ->willReturn($channelType);

        $path = '//http://dev_ce.local/media/cache/avatar_xsmall/bundles/acmedemo/img/logo.png';
        $this->cacheManager
            ->expects($this->never())
            ->method('getBrowserPath')
            ->with('bundles/acmedemo/img/logo.png', 'avatar_xsmall')
            ->willReturn($path);

        $channel = new Channel();
        $channel->setType($integrationTypeName);
        $customer = new Customer();
        $customer->setChannel($channel);

        $icon = $this->customerIconProvider->getIcon($customer);

        $this->assertEmpty($icon);
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
