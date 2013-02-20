<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;

abstract class FlexibleDatagridManager extends DatagridManager
{
    /**
     * @var FlexibleManager
     */
    protected $flexibleManager;

    /**
     * @var string
     */
    protected $flexibleManagerServiceId;

    /**
     * @var Attribute[]
     */
    protected $attributes;

    /**
     * @param FlexibleManager $flexibleManager
     * @param string $serviceId
     */
    public function setFlexibleManager(FlexibleManager $flexibleManager, $serviceId)
    {
        $this->flexibleManager          = $flexibleManager;
        $this->flexibleManagerServiceId = $serviceId;

        // TODO: somehow get from parameters interface
        $this->flexibleManager->setLocale('en');
        $this->flexibleManager->setScope('ecommerce');
    }

    /**
     * @return Attribute[]
     */
    protected function getFlexibleAttributes()
    {
        if (null === $this->attributes) {
            /** @var $attributeRepository \Doctrine\Common\Persistence\ObjectRepository */
            $attributeRepository = $this->flexibleManager->getAttributeRepository();
            $this->attributes = $attributeRepository->findBy(
                array('entityType' => $this->flexibleManager->getFlexibleName())
            );
        }

        return $this->attributes;
    }
}
