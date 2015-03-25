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
        $key =  $group->getName();
        if ($group->getChannel()) {
            $key .= $group->getChannel()->getId();
        }

        if (!isset($this->groupEntityCache[$key])) {
            $this->groupEntityCache[$key] = $group;
        }
        $this->groupEntityCache[$key] = $this->doctrineHelper->merge($this->groupEntityCache[$key]);

        return $this->groupEntityCache[$key];
    }
}
