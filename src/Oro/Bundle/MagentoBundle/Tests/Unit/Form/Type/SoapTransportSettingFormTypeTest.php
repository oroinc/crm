<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use Oro\Bundle\MagentoBundle\Form\EventListener\ConnectorsFormSubscriber;
use Oro\Bundle\MagentoBundle\Form\EventListener\IsDisplayOrderNotesSubscriber;
use Oro\Bundle\MagentoBundle\Form\EventListener\SettingsFormSubscriber;
use Oro\Bundle\MagentoBundle\Form\EventListener\SharedEmailListSubscriber;
use Oro\Bundle\MagentoBundle\Form\Type\SoapTransportSettingFormType;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class SoapTransportSettingFormTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var  SoapTransport | \PHPUnit\Framework\MockObject\MockObject */
    protected $soapTransport;

    /** @var  SettingsFormSubscriber | \PHPUnit\Framework\MockObject\MockObject */
    protected $settingsFormSubscriber;

    /** @var  TypesRegistry| \PHPUnit\Framework\MockObject\MockObject */
    protected $typesRegistry;

    /** @var  SoapTransportSettingFormType */
    protected $type;

    protected function setUp(): void
    {
        $this->soapTransport = $this->getMockBuilder(SoapTransport::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->settingsFormSubscriber = $this->getMockBuilder(SettingsFormSubscriber::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->typesRegistry = $this->getMockBuilder(TypesRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new SoapTransportSettingFormType(
            $this->soapTransport,
            $this->settingsFormSubscriber,
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

        $builder->expects($this->exactly(6))
            ->method('create')
            ->willReturn($builder);

        $builder->expects($this->exactly(1))
            ->method('addViewTransformer')
            ->willReturn($builder);

        $this->type->buildForm($builder, []);
    }

    public function testConfigureOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $this->soapTransport->expects($this->once())
            ->method('getSettingsEntityFQCN')
            ->willReturn(MagentoSoapTransport::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class' => MagentoSoapTransport::class,
                ]
            );

        $this->type->configureOptions($resolver);
    }
}
