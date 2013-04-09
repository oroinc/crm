<?php

namespace Oro\Bundle\NavigationBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\NavigationBundle\Provider\TitleService;

class TitleExtension extends \Twig_Extension
{
    const EXT_NAME = 'oro_title';

    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @var TitleService
     */
    protected $titleService;

    /**
     * @param TitleService $titleService
     * @param ContainerInterface $container
     */
    public function __construct(TitleService $titleService, ContainerInterface $container)
    {
        $this->titleService = $titleService;
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
        $this->titleService
            ->setTemplate($title)
            ->generate($options);

        return $this->titleService->render();
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
