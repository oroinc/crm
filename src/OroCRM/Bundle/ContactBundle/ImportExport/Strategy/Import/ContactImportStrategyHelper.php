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
        return $this->managerRegistry->getRepository($entityName);
    }

    /**
     * @param User $user
     * @return User|null
     */
    public function getUserOrNull(User $user)
    {
        $existingUser = null;
        $username = $user->getUsername();

        if ($username) {
            $existingUser = $this->getEntityRepository('OroUserBundle:User')->findOneBy(
                array('username' => $username)
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
        $existingAddressType = null;
        $addressTypeName = $addressType->getName();
        if ($addressTypeName) {
            $existingAddressType = $this->getEntityRepository('OroAddressBundle:AddressType')->find($addressTypeName);
        }

        return $existingAddressType ?: null;
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
            $existingRegion = $this->getEntityRepository('OroAddressBundle:Region')->find($combinedCode);
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
            $existingCountry = $this->getEntityRepository('OroAddressBundle:Country')->find($iso2Code);
        }

        return $existingCountry ?: null;
    }

    /**
     * @param Group $group
     * @return null|Group
     */
    public function getGroupOrNull(Group $group)
    {
        $existingGroup = null;
        $groupLabel = $group->getLabel();
        if ($groupLabel) {
            $existingGroup = $this->getEntityRepository('OroCRMContactBundle:Group')->findOneBy(
                array('label' => $groupLabel)
            );
        }

        return $existingGroup ?: null;
    }

    /**
     * @param Source $source
     * @return null|Source
     */
    public function getSourceOrNull(Source $source)
    {
        $existingSource = null;
        $sourceName = $source->getName();
        if ($sourceName) {
            $existingSource = $this->getEntityRepository('OroCRMContactBundle:Source')->find($sourceName);
        }

        return $existingSource ?: null;
    }

    /**
     * @param Method $method
     * @return null|Method
     */
    public function getMethodOrNull(Method $method)
    {
        $existingMethod = null;
        $methodName = $method->getName();
        if ($methodName) {
            $existingMethod = $this->getEntityRepository('OroCRMContactBundle:Method')->find($methodName);
        }

        return $existingMethod ?: null;
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

        return $this->getEntityRepository('OroUserBundle:User')->find($user->getId());
    }
}
