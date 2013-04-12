<?php

namespace Oro\Bundle\NavigationBundle\Twig;

use Oro\Bundle\NavigationBundle\Provider\TitleServiceInterface;

class TitleExtension extends \Twig_Extension
{
    const EXT_NAME = 'oro_title';

    /**
     * @var TitleServiceInterface
     */
    protected $titleService;

    /**
     * @param TitleServiceInterface $titleService
     */
    public function __construct(TitleServiceInterface $titleService)
    {
        $this->titleService = $titleService;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'oro_title_render' => new \Twig_Function_Method($this, 'render', array('is_safe' => array('html'))),
            'oro_title_render_stored' => new \Twig_Function_Method($this, 'renderStored', array('is_safe' => array('html'))),
            'oro_title_render_serialized' => new \Twig_Function_Method($this, 'renderSerialized', array('is_safe' => array('html'))),
        );
    }

    /**
     * Register new token parser
     *
     * @return array
     */
    public function getTokenParsers()
    {
        return array(
            new TitleSetTokenParser()
        );
    }

    /**
     * Renders title
     *
     * @return string
     */
    public function render()
    {
        return $this->titleService->render();
    }

    /**
     * Set title options
     *
     * @param array $options
     * @return $this
     */
    public function set(array $options = array())
    {
        return $this->titleService->setData($options);
    }

    /**
     * Renders title from saved json string
     *
     * @param string $titleData json encoded string
     *
     * @return string
     */
    public function renderStored($titleData)
    {
        return $this->titleService->renderStored($titleData);
    }

    /**
     * Returns json serialized data
     *
     * @return string
     */
    public function renderSerialized()
    {
        return $this->titleService->getSerialized();
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
