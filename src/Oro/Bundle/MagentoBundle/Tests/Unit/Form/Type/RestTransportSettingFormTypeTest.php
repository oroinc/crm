<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\MagentoBundle\Entity\MagentoRestTransport;
use Oro\Bundle\MagentoBundle\Form\EventListener\ConnectorsFormSubscriber;
use Oro\Bundle\MagentoBundle\Form\EventListener\IsDisplayOrderNotesSubscriber;
use Oro\Bundle\MagentoBundle\Form\EventListener\SettingsFormSubscriber;
use Oro\Bundle\MagentoBundle\Form\EventListener\SharedEmailListSubscriber;
use Oro\Bundle\MagentoBundle\Form\Type\RestTransportSettingFormType;
use Oro\Bundle\MagentoBundle\Provider\Transport\RestTransport;

class RestTransportSettingFormTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var  RestTransport | \PHPUnit\Framework\MockObject\MockObject */
    protected $restTransport;

    /** @var  TypesRegistry| \PHPUnit\Framework\MockObject\MockObject */
    protected $typesRegistry;

    /** @var SettingsFormSubscriber | \PHPUnit\Framework\MockObject\MockObject */
    protected $settingFormSubscriber;

    /** @var  RestTransportSettingFormType */
    protected $type;

    protected function setUp(): void
    {
        $this->restTransport = $this->getMockBuilder(RestTransport::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->typesRegistry = $this->getMockBuilder(TypesRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->settingFormSubscriber = $this->createMock(SettingsFormSubscriber::class);

        $this->type = new RestTransportSettingFormType(
            $this->restTransport,
            $this->settingFormSubscriber,
            $this->typesRegistry
        );
    }

    protected function tearDown(): void
    {
        unset($this->type);
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->exactly(4))
            ->method('addEventSubscriber')
            ->with($this->logicalOr(
                $this->isInstanceOf(SettingsFormSubscriber::class),
                $this->isInstanceOf(ConnectorsFormSubscriber::class),
                $this->isInstanceOf(SharedEmailListSubscriber::class),
                $this->isInstanceOf(IsDisplayOrderNotesSubscriber::class)
            ))->willReturnSelf();

        $builder->expects($this->any())
            ->method('add')
            ->willReturn($builder);

        $builder->expects($this->exactly(4))
            ->method('create')
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('addViewTransformer')
            ->willReturn($builder);

        $this->type->buildForm($builder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $this->restTransport->expects($this->once())
            ->method('getSettingsEntityFQCN')
            ->willReturn(MagentoRestTransport::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class' => MagentoRestTransport::class,
                ]
            );

        $this->type->configureOptions($resolver);
    }
}
