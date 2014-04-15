<?php

namespace OroCRM\Bundle\MagentoBundle\Datagrid;

use Doctrine\ORM\EntityRepository;

use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;

class MagentoDatagridHelper
{
    /**
     * Returns query builder callback for magento channels
     *
     * @return callable
     */
    public function getMagentoChannelsQueryBuilder()
    {
        return function (EntityRepository $er) {
            return $er->createQueryBuilder('c')
                ->where('c.type = :type')
                ->setParameter('type', ChannelType::TYPE);
        };
    }
}
