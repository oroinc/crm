<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Strategy;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Util\ClassUtils;

class ContactAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var ContactAddress|null
     */
    protected $primaryAddress;

    /**
     * @var ContactEmail|null
     */
    protected $primaryEmail;

    /**
     * @var ContactPhone|null
     */
    protected $primaryPhone;

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
            $this->updateAddresses($entity);
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
                /** @var \Oro\Bundle\AddressBundle\Entity\Country $existingCountry */
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
                /** @var \Oro\Bundle\AddressBundle\Entity\Region $existingRegion */
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

                /** @var \Oro\Bundle\AddressBundle\Entity\AddressType $existingAddressType */
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

    /**
     * @param Contact $entity
     * @return Contact
     */
    protected function beforeProcessEntity($entity)
    {
        // need to manually set empty types to skip merge from existing entities
        $itemData = $this->context->getValue('itemData');

        if (!empty($itemData['addresses'])) {
            foreach ($itemData['addresses'] as $key => $address) {
                if (!isset($address['types'])) {
                    $itemData['addresses'][$key]['types'] = array();
                }
            }

            $this->context->setValue('itemData', $itemData);
        }

        return $entity;
    }

    /**
     * @param Contact $entity
     * @return Contact
     */
    protected function afterProcessEntity($entity)
    {
        // there can be only one primary entity
        foreach (array('address', 'email', 'phone') as $contactName) {
            /** @var Collection $contacts */
            $contactGetter  = $this->generateGetter($entity, $contactName);
            $contactSetter  = 'setPrimary' . ucfirst($contactName);
            $contacts       = $entity->$contactGetter();
            $primaryContact = $this->getPrimaryEntity($contacts);

            if ($primaryContact) {
                $entity->$contactSetter($primaryContact);
            } elseif ($contacts->count() > 0) {
                $entity->$contactSetter($contacts->first());
            }
        }

        return $entity;
    }

    /**
     * @param object $entity
     * @param string $neededName
     * @return string
     */
    protected function generateGetter($entity, $neededName)
    {
        $getter = 'get' . ucfirst($neededName);
        return method_exists($entity, $getter . 's') ? $getter . 's' : $getter . 'es';
    }

    /**
     * @param Collection|AbstractAddress[] $entities
     * @return AbstractAddress|null
     */
    protected function getPrimaryEntity($entities)
    {
        $primaryEntities = array();

        if ($entities) {
            foreach ($entities as $entity) {
                if ($entity->isPrimary()) {
                    $primaryEntities[] = $entity;
                }
            }
        }

        return !empty($primaryEntities) ? current($primaryEntities) : null;
    }
}
