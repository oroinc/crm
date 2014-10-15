<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Strategy;

use Doctrine\Common\Collections\Collection;

use Oro\Bundle\FormBundle\Entity\PrimaryItem;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

class ContactImportHelper
{
    /**
     * @param Contact $entity
     */
    public function updateScalars(Contact $entity)
    {
        // update gender
        $gender = $entity->getGender();
        if (null !== $gender) {
            $gender = strtolower($gender);
            switch ($gender) {
                case 'm':
                    $gender = 'male';
                    break;
                case 'f':
                    $gender = 'female';
                    break;
            }
            $entity->setGender($gender);
        }
    }

    /**
     * There can be only one primary entity
     *
     * @param Contact $entity
     */
    public function updatePrimaryEntities(Contact $entity)
    {
        // update addresses
        $addresses = $entity->getAddresses();
        $primaryAddress = $this->getPrimaryEntity($addresses);

        if ($primaryAddress) {
            $entity->setPrimaryAddress($primaryAddress);
        } elseif ($addresses->count() > 0) {
            $entity->setPrimaryAddress($addresses->first());
        }

        // update emails
        $emails = $entity->getEmails();
        $primaryEmail = $this->getPrimaryEntity($emails);

        if ($primaryEmail) {
            $entity->setPrimaryEmail($primaryEmail);
        } elseif ($emails->count() > 0) {
            $entity->setPrimaryEmail($emails->first());
        }

        // update phones
        $phones = $entity->getPhones();
        $primaryPhone = $this->getPrimaryEntity($phones);

        if ($primaryPhone) {
            $entity->setPrimaryPhone($primaryPhone);
        } elseif ($phones->count() > 0) {
            $entity->setPrimaryPhone($phones->first());
        }
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
