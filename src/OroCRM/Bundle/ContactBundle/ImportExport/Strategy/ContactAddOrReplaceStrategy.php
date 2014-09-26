<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Strategy;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\FormBundle\Entity\PrimaryItem;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;

class ContactAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
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
        $className = ClassUtils::getClass($originEntity);

        return $this->databaseHelper->findOneBy($className, $criteria);
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
        $addresses = $entity->getAddresses();
        $primaryAddress = $this->getPrimaryEntity($addresses);

        if ($primaryAddress) {
            /** @var ContactAddress $primaryAddress */
            $entity->setPrimaryAddress($primaryAddress);
        } elseif ($addresses->count() > 0) {
            $entity->setPrimaryAddress($addresses->first());
        }

        $emails = $entity->getEmails();
        $primaryEmail = $this->getPrimaryEntity($emails);

        if ($primaryEmail) {
            /** @var ContactEmail $primaryEmail */
            $entity->setPrimaryEmail($primaryEmail);
        } elseif ($emails->count() > 0) {
            $entity->setPrimaryEmail($emails->first());
        }

        $phones = $entity->getPhones();
        $primaryPhone = $this->getPrimaryEntity($phones);

        if ($primaryPhone) {
            /** @var ContactPhone $primaryPhone */
            $entity->setPrimaryPhone($primaryPhone);
        } elseif ($phones->count() > 0) {
            $entity->setPrimaryPhone($phones->first());
        }

        return $entity;
    }

    /**
     * @param Collection|PrimaryItem[] $entities
     * @return PrimaryItem|null
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
