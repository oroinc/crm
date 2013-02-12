<?php

namespace Oro\Bundle\DataFlowBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Configuration repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ConfigurationRepository extends EntityRepository
{

    /**
     * Retrieve configuration
     *
     * @param string $typeName    the configuration FQCN
     * @param string $description the configuration description
     */
    public function findOneByTypeAndDescription($typeName, $description)
    {
        $criteria = array('typeName' => $typeName, 'description' => $description);

        return $this->findOneBy($criteria);
    }
}
