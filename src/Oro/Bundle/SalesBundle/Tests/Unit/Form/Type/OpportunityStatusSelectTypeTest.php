<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\SalesBundle\Form\Type\OpportunityStatusSelectType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpportunityStatusSelectTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider probabilitiesDataProvider
     */
    public function testViewShouldHaveProbabilitiesDataAttributes(array $probabilities)
    {
        $type = $this->getFormType();
        $formView = new FormView();
        $form = $this->createMock(FormInterface::class);

        $type->buildView($formView, $form, ['probabilities' => $probabilities]);

        $this->assertJsonStringEqualsJsonString(
            json_encode($probabilities),
            $formView->vars['attr']['data-probabilities']
        );
    }

    public function testShouldHaveProbabilitiesAsDefaultOption()
    {
        $type = $this->getFormType(['won' => 1.0, 'lost' => 0.0]);
        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $options = $resolver->resolve();

        $this->assertEquals(['probabilities' => ['won' => 100.0, 'lost' => 0.0]], $options);
    }

    public function probabilitiesDataProvider(): array
    {
        return [
            [
                ['won' => 1.0, 'lost' => 0.0],
            ]
        ];
    }

    public function testShouldFilterNullProbabilities()
    {
        $type = $this->getFormType(['won' => 1.0, 'lost' => 0.0, 'empty' => null]);
        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);
        $options = $resolver->resolve();

        $this->assertEquals(['probabilities' => ['won' => 100.0, 'lost' => 0.0]], $options);
    }

    private function getFormType(array $probabilities = []): OpportunityStatusSelectType
    {
        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->any())
            ->method('get')
            ->willReturn($probabilities);

        return new OpportunityStatusSelectType($configManager);
    }
}
