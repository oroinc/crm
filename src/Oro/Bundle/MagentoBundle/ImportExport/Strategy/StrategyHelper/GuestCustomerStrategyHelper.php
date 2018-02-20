<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper;

use Oro\Bundle\ImportExportBundle\Field\DatabaseHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\LocaleBundle\Model\FirstNameInterface;
use Oro\Bundle\LocaleBundle\Model\LastNameInterface;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\IntegrationAwareInterface;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;

class GuestCustomerStrategyHelper
{
    /** @var DatabaseHelper */
    protected $databaseHelper;

    /**
     * @param DatabaseHelper $databaseHelper
     */
    public function __construct(DatabaseHelper $databaseHelper)
    {
        $this->databaseHelper = $databaseHelper;
    }

    /**
     * @param Customer $customer
     *
     * @return bool
     */
    public function isGuestCustomerEmailInSharedList(Customer $customer)
    {
        $channel = $customer->getChannel();

        if (!$channel instanceof Channel) {
            return false;
        }

        $customerEmail = $customer->getEmail();
        return $this->isEmailInSharedList($channel->getId(), $customerEmail);
    }

    /**
     * @param Customer $customer
     * @param array $searchContext
     *
     * @return mixed
     */
    public function getUpdatedSearchContextForGuestCustomers(Customer $customer, array $searchContext)
    {
        if ($this->isGuestCustomerEmailInSharedList($customer)) {
            $searchContext = $this->updateSearchContext($customer, $searchContext);
        }

        return $searchContext;
    }

    /**
     * Update identification values for guest customer
     * for entities containing customer like Order and Cart
     * or for Customer entity
     *
     * @param IntegrationAwareInterface $entity
     * @param array                     $identificationValues
     * @param null|string               $email
     *
     * @return array
     */
    public function updateIdentityValuesByCustomerOrParentEntity(
        IntegrationAwareInterface $entity,
        array $identificationValues,
        $email = null
    ) {
        $email = $this->getEmail($identificationValues, $email);
        if (null === $email) {
            return $identificationValues;
        }

        $channel = $entity->getChannel();
        if ($channel && $this->isEmailInSharedList($channel->getId(), $email)) {
            $identificationValues = $this->updateSearchContext($entity, $identificationValues);
        }

        return $identificationValues;
    }

    /**
     * Get existing guest customer
     * Find existing guest customer using entity data for entities containing customer like Order and Cart
     *
     * @param IntegrationAwareInterface $entity
     * @param array $searchContext
     * @param null|string   $email
     *
     * @return null|Customer
     */
    public function findExistingGuestCustomerByContext(
        IntegrationAwareInterface $entity,
        array $searchContext,
        $email = null
    ) {
        $searchContext = $this->updateIdentityValuesByCustomerOrParentEntity($entity, $searchContext, $email);

        /** @var Customer $existingEntity */
        $existingEntity = $this->databaseHelper->findOneBy(
            Customer::class,
            $searchContext
        );

        return $existingEntity;
    }

    /**
     * @param $channelId
     * @param string  $email
     *
     * @return boolean
     */
    private function isEmailInSharedList($channelId, $email)
    {
        if (!$email) {
            return false;
        }

        $sharedGuestEmailList = null;
        $channel = $this->databaseHelper->findOneBy(Channel::class, ['id' => $channelId]);

        $transport = $channel->getTransport();
        if ($transport instanceof MagentoTransport) {
            $sharedGuestEmailList = $transport->getSharedGuestEmailList();
        }

        return !empty($sharedGuestEmailList) && in_array($email, $sharedGuestEmailList);
    }

    /**
     * @param object $entity
     * @param array $context
     *
     * @return mixed
     */
    private function updateSearchContext($entity, array $context)
    {
        if ($entity instanceof FirstNameInterface) {
            if ($entity->getFirstName()) {
                $context['firstName'] = $entity->getFirstName();
            }
        }
        if ($entity instanceof LastNameInterface) {
            if ($entity->getLastName()) {
                $context['lastName'] = $entity->getLastName();
            }
        }

        return $context;
    }

    /**
     * @param null|string   $email
     * @param array         $identificationData
     *
     * @return null|string
     */
    private function getEmail(array $identificationData, $email)
    {
        if ($email !== null) {
            return $email;
        }

        if (isset($identificationData['email'])) {
            return $identificationData['email'];
        }

        return null;
    }
}
