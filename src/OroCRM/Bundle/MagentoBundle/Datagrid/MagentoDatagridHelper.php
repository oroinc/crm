<?php

namespace OroCRM\Bundle\MagentoBundle\Datagrid;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;

class MagentoDatagridHelper
{
    /** @var EntityManager */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

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

    /**
     * Returns choices for Magento Customer Group filter
     * Labels include integration channel name if any
     *
     * @return array
     */
    public function getMagentoGroupsChoices()
    {
        $er = $this->em->getRepository(CustomerGroup::class);

        $qb = $er->createQueryBuilder('g')
            ->select('g.id as id')
            ->addSelect('CONCAT(g.name, COALESCE(CONCAT(\' (\', gc.name, \')\'), \'\')) as name')
            ->leftJoin('g.channel', 'gc');
        $groups = $qb->getQuery()->getArrayResult();
        $choices = array_combine(array_column($groups, 'id'), array_column($groups, 'name'));

        return $choices;
    }
}
