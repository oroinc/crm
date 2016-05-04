<?php

namespace OroCRM\Bundle\CallBundle\Twig;

use OroCRM\Bundle\CallBundle\Placeholder\LogCallPlaceholderFilter;

class OroCRMCallExtension extends \Twig_Extension
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $logCallPlaceholderFilter;

    /**
     * @param LogCallPlaceholderFilter $logCallPlaceholderFilter
     */
    public function __construct(LogCallPlaceholderFilter $logCallPlaceholderFilter)
    {
        $this->logCallPlaceholderFilter = $logCallPlaceholderFilter;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        $logCallPlaceholderFilter = $this->logCallPlaceholderFilter;
        return array(
            new \Twig_SimpleFunction('isCallLogApplicable', function ($entity) use ($logCallPlaceholderFilter) {
                return $logCallPlaceholderFilter->isApplicable($entity);
            }),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'orocrm_call_extension';
    }
}
