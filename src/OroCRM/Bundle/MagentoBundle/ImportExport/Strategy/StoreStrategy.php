<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;

use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;

class StoreStrategy extends BaseStrategy
{
    /** @var array */
    protected $identityMap = [];

    /** @var array */
    protected $skipAttributes = ['id', 'code', 'website', 'channel'];

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        return $this->getEntityFromCache($entity);
    }

    /**
     * @param Website|Store $entity
     *
     * @return mixed
     */
    protected function getEntityFromCache($entity)
    {
        $className = ClassUtils::getClass($entity);
        $code      = $entity->getCode();
        $channel   = $entity->getChannel();
        $criteria  = ['code' => $code, 'channel' => $channel];

        // first time trying to found in DB and update
        if (empty($this->identityMap[$className][$channel->getId()][$code])) {
            $existingEntity = $this->getEntityByCriteria($criteria, $entity);

            // if loaded from db just update
            if (null !== $existingEntity) {
                $this->strategyHelper->importEntity($existingEntity, $entity, $this->skipAttributes);
                $entity = $existingEntity;
            } else {
                $entity->setId(null);
            }
        } else {
            // use prepared entity
            $entity = $this->identityMap[$className][$channel->getId()][$code];
        }

        /*
         * Always merge website and channel in order to be sure that they are managed
         */

        if ($entity instanceof Store) {
            $website = $entity->getWebsite();
            $entity->setWebsite($this->getEntityFromCache($website));
        }

        $entity = $this->merge($entity);
        $entity->setChannel($this->merge($channel));

        return $this->identityMap[$className][$channel->getId()][$code] = $entity;
    }
}
