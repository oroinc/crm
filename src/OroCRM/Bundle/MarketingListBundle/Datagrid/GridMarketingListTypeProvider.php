<?php

namespace OroCRM\Bundle\MarketingListBundle\Datagrid;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;
use Symfony\Bridge\Doctrine\RegistryInterface;

class GridMarketingListTypeProvider
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Get marketing list types choices.
     *
     * @return array
     */
    public function getListTypeChoices()
    {
        /** @var EntityManager $em */
        $em = $this->registry->getManager();

        /** @var MarketingListType[] $types */
        $types = $em->getRepository('OroCRMMarketingListBundle:MarketingListType')
            ->findBy(array(), array('name' => 'ASC'));

        $results = array();
        foreach ($types as $type) {
            $results[$type->getName()] = $type->getLabel();
        }

        return $results;
    }
}
