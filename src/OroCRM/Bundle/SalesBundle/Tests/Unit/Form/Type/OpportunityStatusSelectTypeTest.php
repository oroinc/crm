<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

use OroCRM\Bundle\SalesBundle\Form\Type\OpportunityStatusSelectType;

class OpportunityStatusSelectTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider probabilitiesDataProvider
     *
     * @param array $probabilities
     */
    public function testViewShouldHaveProbabilitiesDataAttributes(array $probabilities)
    {
        $type = $this->getFormType();
        $formView = $this->getFormView();
        $form = $this->getFormMock();

        $type->buildView($formView, $form, ['probabilities' => $probabilities]);

        $this->assertJsonStringEqualsJsonString(
            json_encode($probabilities),
            $formView->vars['attr']['data-probabilities']
        );
    }

    /**
     * @dataProvider probabilitiesDataProvider
     *
     * @param array $probabilities
     */
    public function testShouldHaveProbabilitiesAsDefaultOption(array $probabilities)
    {
        $type = $this->getFormType($probabilities);
        $resolver = $this->getOptionsResolver();

        $type->configureOptions($resolver);
        $options = $resolver->resolve();

        $this->assertEquals(['probabilities' => $probabilities], $options);
    }

    public function probabilitiesDataProvider()
    {
        return [
            [
                ['won' => 1.0, 'lost' => 0.0],
            ]
        ];
    }

    /**
     * @param array $probabilities
     *
     * @return OpportunityStatusSelectType
     */
    private function getFormType(array $probabilities = array())
    {
        $configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configManager->expects($this->any())
            ->method('get')
            ->willReturn($probabilities);

        $type = new OpportunityStatusSelectType($configManager);

        return $type;
    }

    /**
     * @return FormView
     */
    private function getFormView()
    {
        return new FormView();
    }

    /**
     * @return FormInterface
     */
    private function getFormMock()
    {
        return $this->getMock(FormInterface::class);
    }

    /**
     * @return OptionsResolver
     */
    private function getOptionsResolver()
    {
        return new OptionsResolver();
    }
}
