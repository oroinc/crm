<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Store;

class StoreStrategy extends BaseStrategy
{
    const ENTITY_NAME = 'OroCRMMagentoBundle:Store';

    /** @var array */
    protected $storeEntityCache = [];

    /**
     * {@inheritdoc}
     * @param Store $entity
     */
    public function process($entity)
    {
        // do not allow to change code/website name by imported entity
        $doNotUpdateFields = ['id', 'code', 'name', 'website'];
        $code = $entity->getCode();

        $criteria = ['code' => $code, 'channel' => $entity->getChannel()];

        if (empty($this->storeEntityCache[$code])) {
            $this->storeEntityCache[$code] = $this->getEntityByCriteria($criteria, $entity);

            if ($this->storeEntityCache[$code]) {
                $this->strategyHelper->importEntity($this->storeEntityCache[$code], $entity, $doNotUpdateFields);
            } else {
                $this->storeEntityCache[$code] = $entity->setId(null);
            }

            $this->storeEntityCache[$code] = $this->merge($this->storeEntityCache[$code]);
        }

        return $this->storeEntityCache[$code];
    }
}
