<?php

namespace Oro\Bundle\NavigationBundle\Twig;

use Oro\Bundle\NavigationBundle\Provider\TitleService;
use JMS\Serializer\Serializer;

class TitleExtension extends \Twig_Extension
{
    const EXT_NAME = 'oro_title';

    /**
     * @var TitleService
     */
    protected $titleService;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @param TitleService $titleService
     * @param \JMS\Serializer\Serializer $serializer
     */
    public function __construct(TitleService $titleService, Serializer $serializer)
    {
        $this->titleService = $titleService;
        $this->serializer = $serializer;
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
        );
    }

    /**
     * Renders a title
     *
     * @param string $titleTemplate
     * @param array $options
     *
     * @return string
     */
    public function render(array $options = array(), $titleTemplate = null)
    {
        return $this->titleService->render($options, $titleTemplate, true);
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
        $data =  $this->serializer->deserialize($titleData, 'Oro\Bundle\NavigationBundle\Title\StoredTitle', 'json');

        return $this->titleService->render($data->getParams(), $data->getTemplate());
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
