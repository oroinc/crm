<?php

namespace OroCRM\Bundle\MarketingListBundle\Datagrid;

use Symfony\Bridge\Doctrine\RegistryInterface;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;

class GridMarketingListTypeProvider
{
    const MARKETING_LIST_TYPE = 'OroCRMMarketingListBundle:MarketingListType';

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
        /** @var MarketingListType[] $types */
        $types = $this->registry
            ->getManagerForClass(self::MARKETING_LIST_TYPE)
            ->getRepository(self::MARKETING_LIST_TYPE)
            ->findBy(array(), array('name' => 'ASC'));

        $results = array();
        foreach ($types as $type) {
            $results[$type->getName()] = $type->getLabel();
        }

        return $results;
    }
}
