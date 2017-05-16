<?php
namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Type;

use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Form\EventListener\ConnectorsFormSubscriber;
use Oro\Bundle\MagentoBundle\Form\Type\RestTransportSettingFormType;
use Oro\Bundle\MagentoBundle\Provider\Transport\RestTransport;

class RestTransportSettingFormTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var  RestTransport | \PHPUnit_Framework_MockObject_MockObject */
    protected $restTransport;

    /** @var  TypesRegistry| \PHPUnit_Framework_MockObject_MockObject */
    protected $typesRegistry;

    /** @var  RestTransportSettingFormType */
    protected $type;

    protected function setUp()
    {
        $this->restTransport = $this->getMockBuilder(RestTransport::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->typesRegistry = $this->getMockBuilder(TypesRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->type = new RestTransportSettingFormType(
            $this->restTransport,
            $this->typesRegistry
        );
    }

    protected function tearDown()
    {
        unset($this->type);
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $builder->expects($this->once())
            ->method('addEventSubscriber')
            ->with($this->isInstanceOf(ConnectorsFormSubscriber::class));

        $builder->expects($this->any())
            ->method('add')
            ->willReturn($builder);

        $builder->expects($this->exactly(2))
            ->method('create')
            ->willReturn($builder);

        $builder->expects($this->once())
            ->method('addViewTransformer')
            ->willReturn($builder);

        $this->type->buildForm($builder, []);
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $this->restTransport->expects($this->once())
            ->method('getSettingsEntityFQCN')
            ->willReturn(MagentoTransport::class);
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class' => MagentoTransport::class,
                ]
            );

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals(RestTransportSettingFormType::NAME, $this->type->getName());
    }
}
