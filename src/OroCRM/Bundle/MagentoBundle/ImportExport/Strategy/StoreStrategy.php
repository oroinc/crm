<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;

class StoreStrategy extends BaseStrategy
{
    /** @var array */
    protected $storeEntityCache = [];

    /** @var array */
    protected $websiteEntityCache = [];

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        // do not allow to change code/website name by imported entity
        $doNotUpdateFields = ['id', 'code', 'website'];

        return $this->getEntityFromCache('storeEntityCache', $entity, $doNotUpdateFields);
    }

    /**
     * @param string        $storage
     * @param Website|Store $entity
     * @param array         $notImportedAttrs
     *
     * @return mixed
     */
    protected function getEntityFromCache($storage, $entity, $notImportedAttrs = [])
    {
        $code     = $entity->getCode();
        $criteria = ['code' => $code, 'channel' => $entity->getChannel()];
        if (empty($this->{$storage}[$code])) {
            $this->{$storage}[$code] = $this->getEntityByCriteria($criteria, $entity);

            // if loaded from db just update
            if (null !== $this->{$storage}[$code]) {
                $this->strategyHelper->importEntity($this->{$storage}[$code], $entity, $notImportedAttrs);
            } else {
                $this->{$storage}[$code] = $entity->setId(null);
            }

            if ($entity instanceof Store) {
                $notImportedAttrs = ['id', 'code'];
                $website          = $this->{$storage}[$code]->getWebsite();
                $website          = $this->getEntityFromCache('websiteEntityCache', $website, $notImportedAttrs);
                $this->{$storage}[$code]->setWebsite($website);
            }
        }
        $this->{$storage}[$code] = $this->merge($this->{$storage}[$code]);

        return $this->{$storage}[$code];
    }
}
