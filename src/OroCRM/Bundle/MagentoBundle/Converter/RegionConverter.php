<?php

namespace OroCRM\Bundle\MagentoBundle\Converter;

use Doctrine\Common\Persistence\ObjectRepository;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\Region as BAPRegion;

use OroCRM\Bundle\MagentoBundle\Entity\Region;

class RegionConverter
{
    /** @var ObjectRepository */
    protected $repository;

    /** @var array [combinedCode => MagentoRegion] precache magento region by code */
    protected $MRIdentityMap = [];

    /**
     * @paramRegistryInterface $registry
     */
    public function __construct(ObjectRepository $repository)
    {
        $this->repository = $repository;
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
            $this->MRIdentityMap[$code] = $this->repository->findOneBy(['combinedCode' => $code]);
        }

        return $this->MRIdentityMap[$code];
    }
}
