<?php

namespace Oro\Bundle\NavigationBundle\Title\TitleReader;

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class Reader
 * @package Oro\Bundle\NavigationBundle\Title\TitleReader
 */
abstract class Reader
{
    /**
     * @var array
     */
    protected $bundles;

    public function __construct(KernelInterface $kernel)
    {
        $this->bundles = $kernel->getBundles();
    }

    /**
     * Returns data from source
     *
     * @return array
     */
    abstract public function getData();

    /**
     * Get dir array of bundles
     *
     * @return array
     */
    protected function getScanDirectories()
    {
        $directories = false;
        $bundles = $this->bundles;

        foreach ($bundles as $bundle) {
            if (strpos($bundle->getPath(), 'vendor') === false) {
                $directories[] = $bundle->getPath();
            }
        }

        return $directories;
    }
}
