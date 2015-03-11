<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper;

use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;

class CustomerGroupHelper
{
    /**
     * @var array|CustomerGroup[]
     */
    protected $groupEntityCache = [];

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param CustomerGroup $group
     * @return CustomerGroup
     */
    public function getUniqueGroup($group)
    {
        if (!isset($this->groupEntityCache[$group->getName()])) {
            $this->groupEntityCache[$group->getName()] = $group;
        }
        $this->groupEntityCache[$group->getName()] = $this->doctrineHelper
            ->merge($this->groupEntityCache[$group->getName()]);

        return $this->groupEntityCache[$group->getName()];
    }
}
