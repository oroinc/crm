<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Oro\Bundle\AddressBundle\Migrations\Data\ORM\LoadCountryData;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadMagentoCountryData extends LoadCountryData implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    protected function getFileName()
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@OroMagentoBundle/Migrations/Data/ORM' . $this->structureFileName);
    }
}
