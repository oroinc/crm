<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Oro\Bundle\AddressBundle\Migrations\Data\ORM\LoadCountryData;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

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
            ->locateResource('@OroCRMMagentoBundle/Migrations/Data/ORM' . $this->structureFileName);
    }
}
