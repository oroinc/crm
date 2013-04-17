<?php

namespace Oro\Bundle\NavigationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class TitleRepository extends EntityRepository
{
    /**
     * Returns not empty titles array
     *
     * @return array
     */
    public function getNotEmptyTitles()
    {
        return $this
            ->createQueryBuilder('title')
            ->where('title.title <> :title')
            ->setParameter('title', '')
            ->getQuery()
            ->getArrayResult();
    }
}
