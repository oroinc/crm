<?php

namespace Oro\Bundle\NavigationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

class TitleRepository extends EntityRepository
{
    /**
     * Returns array of already stored routes
     *
     * @return array
     */
    public function getExistItems()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->add(
            'select',
            new Expr\Select(
                array(
                    't.route'
                )
            )
        )
        ->add('from', new Expr\From('Oro\Bundle\NavigationBundle\Entity\Title', 't'));

        $result = $this->_em->getConnection()
                        ->query($qb->getQuery()->getSQL());

        $data = $result->fetchAll(\PDO::FETCH_COLUMN);

        return array_fill_keys($data, '');
    }
}
