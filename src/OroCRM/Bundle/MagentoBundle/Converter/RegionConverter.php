<?php

namespace OroCRM\Bundle\MagentoBundle\Converter;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\Region as BAPRegion;

use OroCRM\Bundle\MagentoBundle\Entity\Region;

class RegionConverter
{
    /** @var EntityManager */
    protected $em;

    /** @var array [combinedCode => MagentoRegion] precache magento region by code */
    protected $MRIdentityMap = [];

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param AbstractAddress|BAPRegion $source
     *
     * @return array magento correspondent fields data
     * @throws \InvalidArgumentException
     */
    public function toMagentoData($source)
    {
        $data = ['region' => null, 'region_id' => null];

        if (!$source instanceof AbstractAddress && !$source instanceof BAPRegion) {
            throw new \InvalidArgumentException('Source should be instance of AbstractAddress or Region');
        }

        if ($source instanceof AbstractAddress && !$source->getRegion()) {
            $data['region'] = $source->getRegionText();
        } elseif ($source instanceof AbstractAddress) {
            $source = $source->getRegion();
        }

        if ($source instanceof BAPRegion) {
            $magentoRegion = $this->tryGetMRByCode($source->getCombinedCode());

            if (!$magentoRegion) {
                $data['region'] = $source->getName();
            } else {
                $data['region_id'] = $magentoRegion->getRegionId();
            }
        }

        return $data;
    }

    /**
     * Try to get from local property if exist or load from database afterwards
     *
     * @param string $code
     *
     * @return Region|Null
     */
    protected function tryGetMRByCode($code)
    {
        if (!isset($this->MRIdentityMap[$code]) && !array_key_exists($code, $this->MRIdentityMap)) {
            $repository                 = $this->em->getRepository('OroCRMMagentoBundle:Region');
            $this->MRIdentityMap[$code] = $repository->findOneBy(['combinedCode' => $code]);
        }

        return $this->MRIdentityMap[$code];
    }
}
