<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\ChannelBundle\Form\Extension\IntegrationTypeExtension;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Form\IntegrationFormTypeStub;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Form\Type\ChannelType;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Form\Test\FormIntegrationTestCase;

class IntegrationTypeExtensionTest extends FormIntegrationTestCase
{
    public static $allChoices = ['type 1' => 'type 1', 'type 2' => 'type 2'];

    /** @var IntegrationTypeExtension */
    private $extension;

    /** @var SettingsProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $settingsProvider;

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
            ->willReturn($configValue);
        $form = $this->factory->create(ChannelType::class);
        $form->setData($data);
        $typeView = $form->get('type')->createView();
        $this->assertEquals($expectedChoices, $typeView->vars['choices']);
    }

    public function buildFormProvider(): array
    {
        $entity = $this->createMock(Integration::class);
        $entity->expects($this->any())
            ->method('getId')
            ->willReturn(null);

        $entityUpdate = $this->createMock(Integration::class);
        $entityUpdate->expects($this->any())
            ->method('getId')
            ->willReturn(1);

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

    protected function setUp(): void
    {
        $this->settingsProvider = $this->createMock(SettingsProvider::class);

        $this->extension = new IntegrationTypeExtension($this->settingsProvider);

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $integrationType = new IntegrationFormTypeStub();

        return [
            new PreloadedExtension(
                [ChannelType::class => $integrationType],
                [IntegrationFormTypeStub::class => [$this->extension]]
            )
        ];
    }
}
