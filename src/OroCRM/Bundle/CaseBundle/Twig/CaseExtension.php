<?php

namespace OroCRM\Bundle\CaseBundle\Twig;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class CaseExtension extends \Twig_Extension
{
    const NAME = 'orocrm_case';

    /**
     * @var ConfigManager
     */
    protected $manager;

    public function __construct(ConfigManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('get_view_route', array($this, 'getViewRoute'))
        );
    }

    public function getViewRoute($entityClass)
    {
        $metadata = $this->manager->getEntityMetadata($entityClass);

        return $metadata ? $metadata->routeView : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
