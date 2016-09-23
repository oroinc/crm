<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\ChannelBundle\Form\Extension\IntegrationTypeExtension;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Form\IntegrationFormTypeStub;

use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

class IntegrationTypeExtensionTest extends FormIntegrationTestCase
{
    public static $allChoices = ['type 1' => 'type 1', 'type 2' => 'type 2'];

    /** @var IntegrationTypeExtension */
    protected $extension;

    /** @var SettingsProvider|\PHPUnit_Framework_MockObject_MockObject $settingsProvider */
    protected $settingsProvider;

    /**
     * @dataProvider buildFormProvider
     *
     * @param array            $configValue
     * @param null|Integration $data
     * @param array            $expectedChoices
     */
    public function testBuildForm($configValue, $data, $expectedChoices)
    {
        $this->settingsProvider->expects($this->any())
            ->method('getSourceIntegrationTypes')
            ->will($this->returnValue($configValue));
        $form = $this->factory->create('oro_integration_channel_form');
        $form->setData($data);
        $typeView = $form->get('type')->createView();
        $this->assertEquals($expectedChoices, $typeView->vars['choices']);
    }

    /**
     * @return array
     */
    public function buildFormProvider()
    {
        $entity = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(null));

        $entityUpdate = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();
        $entityUpdate->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        return [
            'data is null' => [
                '$config value' => ['type 1'],
                '$data' => null,
                '$expectedChoices' => [
                    new ChoiceView('type 1', 'type 1', 'type 1'),
                    new ChoiceView('type 2', 'type 2', 'type 2')
                ]
            ],
            'new entity without id' => [
                '$config value' => ['type 1'],
                '$data' => $entity,
                '$expectedChoices' => [
                    new ChoiceView('type 2', 'type 2', 'type 2')
                ]
            ],
            'entity with id' => [
                '$config value' => ['type 1'],
                '$data' => $entityUpdate,
                '$expectedChoices' => [
                    new ChoiceView('type 2', 'type 2', 'type 2')
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->settingsProvider = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();
        $this->extension = new IntegrationTypeExtension($this->settingsProvider);
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $integrationType = new IntegrationFormTypeStub();

        return [
            new PreloadedExtension(
                [$integrationType->getName() => $integrationType],
                [$this->extension->getExtendedType() => [$this->extension]]
            )
        ];
    }
}
