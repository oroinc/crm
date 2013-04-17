<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Twig;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Oro\Bundle\FilterBundle\Twig\RenderJsExtension;

class RenderJsExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_TEMPLATE_NAME        = 'test_template_name';
    const TEST_FIRST_EXISTING_TYPE  = 'test_first_existing_type';
    const TEST_SECOND_EXISTING_TYPE = 'test_second_existing_type';
    const TEST_BLOCK_HTML           = 'test_block_html';
    /**#@-*/

    /**
     * @var RenderJsExtension
     */
    protected $extension;

    /**
     * @var array
     */
    protected $expectedFunctions = array(
        'oro_filter_render_filter_js' => array(
            'callback'          => 'renderFilterJs',
            'safe'              => array('html'),
            'needs_environment' => true
        ),
        'oro_filter_render_header_js' => array(
            'callback'          => 'renderHeaderJs',
            'safe'              => array('html'),
            'needs_environment' => true
        )
    );

    /**
     * @var array
     */
    protected $expectedFilters = array(
        'oro_filter_choices' => array(
            'callback' => 'getChoices'
        )
    );

    protected function setUp()
    {
        $this->extension = new RenderJsExtension(self::TEST_TEMPLATE_NAME);
    }

    protected function tearDown()
    {
        unset($this->extension);
    }

    public function testGetName()
    {
        $this->assertEquals(RenderJsExtension::NAME, $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $twigNode = $this->getMock('\Twig_Node');

        $actualFunctions = $this->extension->getFunctions();

        /** @var $function \Twig_SimpleFunction */
        foreach ($actualFunctions as $function) {
            $functionName = $function->getName();
            $this->assertArrayHasKey($functionName, $this->expectedFunctions);

            $expectedParameters = $this->expectedFunctions[$functionName];
            $this->assertEquals(array($this->extension, $expectedParameters['callback']), $function->getCallable());
            $this->assertEquals($expectedParameters['safe'], $function->getSafe($twigNode));
            $this->assertEquals($expectedParameters['needs_environment'], $function->needsEnvironment());
        }
    }

    /**
     * Data provider for testRenderFilterJs
     *
     * @return array
     */
    public function renderFilterJsDataProvider()
    {
        return array(
            'empty_prefixes' => array(
                '$blockPrefixes' => array()
            ),
            'incorrect_prefixes' => array(
                '$blockPrefixes' => 'not_array_data'
            ),
            'no_existing_block' => array(
                '$blockPrefixes' => array(
                    'not',
                    'existing',
                    'blocks'
                )
            ),
            'existing_blocks' => array(
                '$blockPrefixes' => array(
                    'some',
                    self::TEST_FIRST_EXISTING_TYPE,
                    'existing',
                    self::TEST_SECOND_EXISTING_TYPE,
                    'blocks'
                ),
                '$expectedBlock' => self::TEST_SECOND_EXISTING_TYPE . RenderJsExtension::SUFFIX
            ),
        );
    }

    /**
     * @param array $blockPrefixes
     * @param string|null $expectedBlock
     *
     * @dataProvider renderFilterJsDataProvider
     */
    public function testRenderFilterJs($blockPrefixes, $expectedBlock = null)
    {
        $formView = new FormView();
        $formView->vars = array('block_prefixes' => $blockPrefixes);

        $template = $this->getMockForAbstractClass(
            '\Twig_Template',
            array(),
            '',
            false,
            true,
            true,
            array('hasBlock', 'renderBlock')
        );
        $template->expects($this->any())
            ->method('hasBlock')
            ->will($this->returnCallback(array($this, 'hasBlockCallback')));
        if ($expectedBlock) {
            $template->expects($this->once())
                ->method('renderBlock')
                ->with($expectedBlock, array('formView' => $formView))
                ->will($this->returnValue(self::TEST_BLOCK_HTML));
        }

        $environment = $this->getMock('\Twig_Environment', array('loadTemplate'));
        $environment->expects($this->any())
            ->method('loadTemplate')
            ->with(self::TEST_TEMPLATE_NAME)
            ->will($this->returnValue($template));

        $html = $this->extension->renderFilterJs($environment, $formView);
        if ($expectedBlock) {
            $this->assertEquals(self::TEST_BLOCK_HTML, $html);
        } else {
            $this->assertEmpty($html);
        }
    }

    /**
     * Callback for Twig_Template::hasBlock
     *
     * @param string $blockName
     * @return bool
     */
    public function hasBlockCallback($blockName)
    {
        $existingBlocks = array(
            self::TEST_FIRST_EXISTING_TYPE . RenderJsExtension::SUFFIX,
            self::TEST_SECOND_EXISTING_TYPE . RenderJsExtension::SUFFIX
        );

        return in_array($blockName, $existingBlocks);
    }

    public function testRenderHeaderJs()
    {
        $template = $this->getMockForAbstractClass(
            '\Twig_Template',
            array(),
            '',
            false,
            true,
            true,
            array('renderBlock')
        );
        $template->expects($this->once())
            ->method('renderBlock')
            ->with(RenderJsExtension::HEADER_JAVASCRIPT, array())
            ->will($this->returnValue(self::TEST_BLOCK_HTML));

        $environment = $this->getMock('\Twig_Environment', array('loadTemplate'));
        $environment->expects($this->once())
            ->method('loadTemplate')
            ->with(self::TEST_TEMPLATE_NAME)
            ->will($this->returnValue($template));

        $html = $this->extension->renderHeaderJs($environment);
        $this->assertEquals(self::TEST_BLOCK_HTML, $html);
    }

    public function testGetFilters()
    {
        $actualFilters = $this->extension->getFilters();

        /** @var $filter \Twig_SimpleFilter */
        foreach ($actualFilters as $filter) {
            $filterName = $filter->getName();
            $this->assertArrayHasKey($filterName, $this->expectedFilters);

            $expectedParameters = $this->expectedFilters[$filterName];
            $this->assertEquals(array($this->extension, $expectedParameters['callback']), $filter->getCallable());
        }
    }

    public function testGetChoices()
    {
        $actualData = array(
            new ChoiceView('data_1', 'value_1', 'label_1'),
            new ChoiceView('data_2', 'value_2', 'label_2'),
            'additional' => 'choices',
        );
        $expectedData = array(
            'value_1' => 'label_1',
            'value_2' => 'label_2',
        );

        $this->assertEquals($expectedData, $this->extension->getChoices($actualData));
    }
}
