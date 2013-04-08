<?php

namespace Oro\Bundle\NavigationBundle\Twig;

use Knp\Menu\ItemInterface;
use Knp\Menu\Twig\Helper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TitleExtension extends \Twig_Extension
{
    const EXT_NAME = 'oro_title';

    /**
     * @var Helper $helper
     */
    private $helper;

    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @param Helper $helper
     * @param ContainerInterface $container
     */
    public function __construct(Helper $helper, ContainerInterface $container)
    {
        $this->helper = $helper;
        $this->container = $container;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'oro_title' => new \Twig_Function_Method($this, 'render', array('is_safe' => array('html'))),
        );
    }

    /**
     * Renders a title with the specified renderer.
     *
     * @param string $title
     * @param array $options
     * @param string $renderer
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function render($title, array $options = array(), $renderer = null)
    {
        return $this->helper->render($title, $options, $renderer);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::EXT_NAME;
    }
}
