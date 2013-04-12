<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Tests\FormIntegrationTestCase;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceType;

abstract class AbstractTypeTest extends FormIntegrationTestCase
{
    /**
     * @return TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMockTranslator()
    {
        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $translator->expects($this->any())->method('trans')
            ->with($this->anything(), array(), 'OroFilterBundle')
            ->will($this->returnArgument(0));

        return $translator;
    }

    /**
     * @return OptionsResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMockOptionsResolver()
    {
        return $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
    }

    /**
     * @dataProvider setDefaultOptionsDataProvider
     * @param array $defaultOptions
     * @param array $requiredOptions
     */
    public function testSetDefaultOptions(array $defaultOptions, array $requiredOptions = array())
    {
        $resolver = $this->createMockOptionsResolver();

        if ($defaultOptions) {
            $resolver->expects($this->once())->method('setDefaults')->with($defaultOptions)->will($this->returnSelf());
        }

        if ($requiredOptions) {
            $resolver->expects($this->once())->method('setRequired')->with($requiredOptions)->will($this->returnSelf());
        }

        $this->getTestFormType()->setDefaultOptions($resolver);
    }

    /**
     * Data provider for testBindData
     *
     * @return array
     */
    abstract public function setDefaultOptionsDataProvider();

    /**
     * @dataProvider bindDataProvider
     * @param array $bindData
     * @param array $formData
     * @param array $viewData
     * @param array $customOptions
     */
    public function testBindData(
        array $bindData,
        array $formData,
        array $viewData,
        array $customOptions = array()
    ) {
        $form = $this->factory->create($this->getTestFormType(), null, $customOptions);

        $form->bind($bindData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($formData, $form->getData());

        $view = $form->createView();
        $this->assertEquals($viewData, $view->vars['value']);
    }

    /**
     * Data provider for testBindData
     *
     * @return array
     */
    abstract public function bindDataProvider();

    /**
     * @return FormTypeInterface
     */
    abstract protected function getTestFormType();
}
