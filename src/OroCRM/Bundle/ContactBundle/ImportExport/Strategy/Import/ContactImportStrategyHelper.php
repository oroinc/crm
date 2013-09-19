<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Strategy\Import;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use OroCRM\Bundle\ContactBundle\Entity\Group;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Source;
use OroCRM\Bundle\ContactBundle\Entity\Method;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

class ContactImportStrategyHelper
{
    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var EntityRepository[]
     */
    protected $repositories;

    /**
     * @var Country[]
     */
    protected $countries = array();

    /**
     * @var Region[]
     */
    protected $regions = array();

    /**
     * @var AddressType[]
     */
    protected $addressTypes;

    /**
     * @var Group[]
     */
    protected $groups;

    /**
     * @var Source[]
     */
    protected $sources;

    /**
     * @var Method[]
     */
    protected $methods;

    /**
     * @param SecurityContextInterface $securityContext
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(SecurityContextInterface $securityContext, ManagerRegistry $managerRegistry)
    {
        $this->securityContext = $securityContext;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param string $entityName
     * @return EntityRepository
     */
    protected function getEntityRepository($entityName)
    {
        if (empty($this->repositories[$entityName])) {
            $this->repositories[$entityName] = $this->managerRegistry->getRepository($entityName);
        }

        return $this->repositories[$entityName];
    }

    /**
     * @param User $user
     * @return User|null
     */
    public function getUserOrNull(User $user)
    {
        $existingUser = null;
        $userFirstName = $user->getFirstname();
        $userLastName = $user->getLastname();

        if ($userFirstName && $userLastName) {
            $existingUser = $this->getEntityRepository('OroUserBundle:User')->findOneBy(
                array(
                    'firstName' => $userFirstName,
                    'lastName'  => $userLastName,
                )
            );
        }

        return $existingUser ?: null;
    }

    /**
     * @param AddressType $addressType
     * @return null|AddressType
     */
    public function getAddressTypeOrNull(AddressType $addressType)
    {
        if (null === $this->addressTypes) {
            $types = $this->getEntityRepository('OroAddressBundle:AddressType')->findAll();
            $this->addressTypes = array();
            /** @var AddressType $type */
            foreach ($types as $type) {
                $this->addressTypes[$type->getName()] = $type;
            }
        }

        $addressTypeName = $addressType->getName();

        return !empty($this->addressTypes[$addressTypeName]) ? $this->addressTypes[$addressTypeName] : null;
    }

    /**
     * @param Region $country
     * @return Region|null
     */
    public function getRegionOrNull(Region $country)
    {
        $existingRegion = null;
        $combinedCode = $country->getCombinedCode();
        if ($combinedCode) {
            if (!array_key_exists($combinedCode, $this->regions)) {
                $this->regions[$combinedCode]
                    = $this->getEntityRepository('OroAddressBundle:Region')->find($combinedCode);
            }
            $existingRegion = $this->regions[$combinedCode];
        }

        return $existingRegion ?: null;
    }

    /**
     * @param Country $country
     * @return Country|null
     */
    public function getCountryOrNull(Country $country)
    {
        $existingCountry = null;
        $iso2Code = $country->getIso2Code();
        if ($iso2Code) {
            if (!array_key_exists($iso2Code, $this->countries)) {
                $this->countries[$iso2Code]
                    = $this->getEntityRepository('OroAddressBundle:Country')->find($iso2Code);
            }
            $existingCountry = $this->countries[$iso2Code];
        }

        return $existingCountry ?: null;
    }

    /**
     * @param Group $group
     * @return null|Group
     */
    public function getGroupOrNull(Group $group)
    {
        if (null === $this->groups) {
            $existingGroups = $this->getEntityRepository('OroCRMContactBundle:Group')->findAll();
            $this->groups = array();
            /** @var Group $existingGroup */
            foreach ($existingGroups as $existingGroup) {
                $this->groups[$existingGroup->getLabel()] = $existingGroup;
            }
        }

        $groupLabel = $group->getLabel();

        return !empty($this->groups[$groupLabel]) ? $this->groups[$groupLabel] : null;
    }

    /**
     * @param Source $source
     * @return null|Source
     */
    public function getSourceOrNull(Source $source)
    {
        if (null === $this->sources) {
            $existingSources = $this->getEntityRepository('OroCRMContactBundle:Source')->findAll();
            $this->sources = array();
            /** @var Source $existingSource */
            foreach ($existingSources as $existingSource) {
                $this->sources[$existingSource->getName()] = $existingSource;
            }
        }

        $sourceName = $source->getName();

        return !empty($this->sources[$sourceName]) ? $this->sources[$sourceName] : null;
    }

    /**
     * @param Method $method
     * @return null|Method
     */
    public function getMethodOrNull(Method $method)
    {
        if (null === $this->methods) {
            $existingMethods = $this->getEntityRepository('OroCRMContactBundle:Method')->findAll();
            $this->methods = array();
            /** @var Method $existingMethod */
            foreach ($existingMethods as $existingMethod) {
                $this->methods[$existingMethod->getName()] = $existingMethod;
            }
        }

        $methodName = $method->getName();

        return !empty($this->methods[$methodName]) ? $this->methods[$methodName] : null;
    }

    /**
     * @param Account $account
     * @return Account|null
     */
    public function getAccountOrNull(Account $account)
    {
        $existingAccount = null;
        $accountName = $account->getName();
        if ($accountName) {
            $existingAccount = $this->getEntityRepository('OroCRMAccountBundle:Account')->findOneBy(
                array('name' => $accountName)
            );
        }

        return $existingAccount ?: null;
    }

    /**
     * @param Contact $contact
     * @return Contact|null
     */
    public function getContactOrNull(Contact $contact)
    {
        $existingContact = null;
        $contactId = $contact->getId();
        if ($contactId) {
            $existingContact = $this->getEntityRepository('OroCRMContactBundle:Contact')->find($contactId);
        }

        return $existingContact ?: null;
    }

    /**
     * @return User|null
     */
    public function getSecurityContextUserOrNull()
    {
        $token = $this->securityContext->getToken();
        if (!$token) {
            return null;
        }

        $user = $token->getUser();
        if (!$user) {
            return null;
        }

        return $user;
    }
}
