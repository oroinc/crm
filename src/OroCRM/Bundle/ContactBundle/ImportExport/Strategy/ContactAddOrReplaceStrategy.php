<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Strategy;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Util\ClassUtils;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;

class ContactAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function setRegistry(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $entity = parent::process($entity);

        if ($entity) {
            $this
                ->updateAddresses($entity);
        }

        return $entity;
    }

    /**
     * @param Contact $contact
     * @return ContactAddOrReplaceStrategy
     */
    protected function updateAddresses(Contact $contact)
    {
        /** @var ContactAddress $contactAddress */
        foreach ($contact->getAddresses() as $contactAddress) {
            // update country
            $country = $contactAddress->getCountry();
            if ($country) {
                $existingCountry = $this->getEntity(
                    $country,
                    ['iso2Code' => $country->getIso2Code()]
                );

                $contactAddress->setCountry($existingCountry);
            } else {
                $contactAddress->setCountry(null);
            }

            // update region
            $region = $contactAddress->getRegion();
            if ($region) {
                $existingRegion = $this->getEntity(
                    $region,
                    ['combinedCode' => $region->getCombinedCode()]
                );

                $contactAddress->setRegion($existingRegion);
            } else {
                $contactAddress->setRegion(null);
            }

            // update address types
            foreach ($contactAddress->getTypes() as $addressType) {
                $contactAddress->removeType($addressType);
                $existingAddressType = $this->getEntity(
                    $addressType,
                    ['name' => $addressType->getName()]
                );

                if ($existingAddressType) {
                    $contactAddress->addType($existingAddressType);
                }
            }
        }

        return $this;
    }

    /**
     * @param object $originEntity
     * @param array  $criteria
     * @throws \RuntimeException
     * @return object|null
     */
    protected function getEntity($originEntity, $criteria)
    {
        if (!$this->registry) {
            throw new \RuntimeException('Registry was not set');
        }

        $className = ClassUtils::getRealClass($originEntity);

        return $this->registry
            ->getManagerForClass($className)
            ->getRepository($className)
            ->findOneBy($criteria);
    }
}
