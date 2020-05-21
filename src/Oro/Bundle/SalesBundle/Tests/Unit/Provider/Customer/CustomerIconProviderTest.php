<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Provider\Customer;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Entity\ConfigModelIndexValue;
use Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel;
use Oro\Bundle\SalesBundle\Provider\Customer\CustomerIconProvider;
use Oro\Bundle\UIBundle\Model\Image;

class CustomerIconProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerIconProvider */
    protected $customerIconProvider;

    protected function setUp(): void
    {
        $entityConfigs = [
            'Oro\Bundle\EntityConfigBundle\Entity\EntityConfigModel' => [
                'icon' => 'fa-class',
            ],
            'Oro\Bundle\EntityConfigBundle\Entity\ConfigModelIndexValue' => [],
        ];

        $configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $configProvider->expects($this->any())
            ->method('hasConfig')
            ->will($this->returnCallback(function ($className) use ($entityConfigs) {
                return isset($entityConfigs[$className]);
            }));
        $configProvider->expects($this->any())
            ->method('getConfig')
            ->will($this->returnCallback(function ($className) use ($entityConfigs) {
                return new Config(
                    $this->createMock('Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface'),
                    $entityConfigs[$className]
                );
            }));

        $this->customerIconProvider = new CustomerIconProvider($configProvider);
    }

    /**
     * @dataProvider getIconProvider
     */
    public function testGetIcon($entity, $expectedImage)
    {
        $this->assertEquals(
            $expectedImage,
            $this->customerIconProvider->getIcon($entity)
        );
    }

    public function getIconProvider()
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
