<?php

namespace OroCRM\Bundle\SalesBundle\ImportExport\TemplateFixture;

use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\LeadAddress;
use OroCRM\Bundle\SalesBundle\Entity\LeadStatus;

class LeadFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return 'OroCRM\Bundle\SalesBundle\Entity\Lead';
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getEntityData('Jerry Coleman');
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($key)
    {
        return new Lead();
    }

    /**
     * @param string $key
     * @param Lead   $entity
     */
    public function fillEntityData($key, $entity)
    {
        $userRepo         = $this->templateManager->getEntityRepository('Oro\Bundle\UserBundle\Entity\User');
        $customerRepo     = $this->templateManager->getEntityRepository('OroCRM\Bundle\SalesBundle\Entity\B2bCustomer');
        $contactRepo      = $this->templateManager->getEntityRepository('OroCRM\Bundle\ContactBundle\Entity\Contact');
        $channelRepo      = $this->templateManager->getEntityRepository('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $organizationRepo = $this->templateManager
            ->getEntityRepository('Oro\Bundle\OrganizationBundle\Entity\Organization');

        switch ($key) {
            case 'Jerry Coleman':
                $entity->setName('Oro Inc. Lead Name');
                $entity->setCompanyName('Oro Inc.');
                $entity->setOwner($userRepo->getEntity('John Doo'));
                $entity->setOrganization($organizationRepo->getEntity('default'));
                $entity->setDataChannel($channelRepo->getEntity('Sales channel|b2b'));
                $entity->setCreatedAt(new \DateTime());
                $entity->setUpdatedAt(new \DateTime());
                $entity->setCustomer($customerRepo->getEntity('Jerry Coleman'));
                $entity->setContact($contactRepo->getEntity('Jerry Coleman'));
                $primaryAddress = $this->createLeadAddress(1);
                $entity->addAddress($primaryAddress);
                $entity->addAddress($this->createLeadAddress(2));
                $entity->addAddress($this->createLeadAddress(3));
                $entity->setPrimaryAddress($primaryAddress);
                $entity->setEmail('JerryAColeman@armyspy.com');
                $entity->setNamePrefix('Mr.');
                $entity->setFirstName('Jerry');
                $entity->setLastName('Coleman');
                $entity->setNameSuffix('Jr.');
                $entity->setStatus(new LeadStatus('New'));
                $entity->setJobTitle('Manager');
                $entity->setPhoneNumber('585-255-1127');
                $entity->setWebsite('http://orocrm.com');
                $entity->setNumberOfEmployees(100);
                $entity->setIndustry('Internet');

                return;
        }

        parent::fillEntityData($key, $entity);
    }

    /**
     * @param int $number
     *
     * @return LeadAddress
     *
     * @throws \LogicException
     */
    protected function createLeadAddress($number)
    {
        $countryRepo = $this->templateManager
            ->getEntityRepository('Oro\Bundle\AddressBundle\Entity\Country');
        $regionRepo  = $this->templateManager
            ->getEntityRepository('Oro\Bundle\AddressBundle\Entity\Region');

        $entity = new LeadAddress();

        $entity
            ->setFirstName('Jerry')
            ->setLastName('Coleman');

        switch ($number) {
            case 1:
                $entity
                    ->setCity('Rochester')
                    ->setStreet('1215 Caldwell Road')
                    ->setPostalCode('14608')
                    ->setRegion($regionRepo->getEntity('NY'))
                    ->setCountry($countryRepo->getEntity('US'));
                break;
            case 2:
                $entity
                    ->setCity('New York')
                    ->setStreet('4677 Pallet Street')
                    ->setPostalCode('10011')
                    ->setRegion($regionRepo->getEntity('NY'))
                    ->setCountry($countryRepo->getEntity('US'));
                break;
            case 3:
                $entity
                    ->setCity('New York')
                    ->setStreet('52 Jarvisville Road')
                    ->setPostalCode('11590')
                    ->setRegion($regionRepo->getEntity('NY'))
                    ->setCountry($countryRepo->getEntity('US'));
                break;
            default:
                throw new \LogicException(
                    sprintf(
                        'Undefined lead address. Number: %d.',
                        $number
                    )
                );
        }

        return $entity;
    }
}
