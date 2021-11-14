<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider\Customer;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModelIndexValue;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\SalesBundle\Provider\Customer\CustomerIconProvider;
use Oro\Bundle\UIBundle\Model\Image;

class CustomerIconProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerIconProvider */
    private $customerIconProvider;

    protected function setUp(): void
    {
        $entityConfigs = [
            EntityConfigModel::class     => [
                'icon' => 'fa-class',
            ],
            ConfigModelIndexValue::class => [],
        ];

        $configProvider = $this->createMock(ConfigProvider::class);
        $configProvider->expects($this->any())
            ->method('hasConfig')
            ->willReturnCallback(function ($className) use ($entityConfigs) {
                return isset($entityConfigs[$className]);
            });
        $configProvider->expects($this->any())
            ->method('getConfig')
            ->willReturnCallback(function ($className) use ($entityConfigs) {
                return new Config(
                    $this->createMock(ConfigIdInterface::class),
                    $entityConfigs[$className]
                );
            });

        $this->customerIconProvider = new CustomerIconProvider($configProvider);
    }

    /**
     * @dataProvider getIconProvider
     */
    public function testGetIcon(object $entity, ?Image $expectedImage)
    {
        $this->assertEquals(
            $expectedImage,
            $this->customerIconProvider->getIcon($entity)
        );
    }

    public function getIconProvider(): array
    {
        return [
            'entity with icon config' => [
                new EntityConfigModel(),
                new Image(Image::TYPE_ICON, ['class' => 'fa-class']),
            ],
            'entity without icon config' => [
                new ConfigModelIndexValue(),
                null,
            ],
            'entity without config' => [
                new \stdClass(),
                null,
            ],
        ];
    }
}
