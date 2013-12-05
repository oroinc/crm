<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\CustomerNormalizer;

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
        $doNotUpdateFields = ['id', 'code', 'name'];
        $code = $entity->getCode();

        if (empty($this->storeEntityCache[$code])) {
            $this->storeEntityCache[$code] = $this->findAndReplaceEntity(
                $entity,
                self::ENTITY_NAME,
                'code',
                $doNotUpdateFields
            );

            $this->storeEntityCache[$code] = $this->merge($this->storeEntityCache[$code]);
        }

        return $this->storeEntityCache[$code];
    }
}
