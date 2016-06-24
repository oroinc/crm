<?php

namespace OroCRM\Bundle\SalesBundle\ImportExport\TemplateFixture;

use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
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
        $addressRepo      = $this->templateManager->getEntityRepository('Oro\Bundle\AddressBundle\Entity\Address');
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
                $entity->setAddress($addressRepo->getEntity('Jerry Coleman'));
                $entity->setEmail('JerryAColeman@armyspy.com');
                $entity->setNamePrefix('Mr.');
                $entity->setFirstName('Jerry');
                $entity->setLastName('Coleman');
                $entity->setNameSuffix('Jr.');
                $entity->setStatus(new LeadStatus('New'));
                $entity->setJobTitle('Manager');
                $entity->addPhone('585-255-1127');
                $entity->setWebsite('http://orocrm.com');
                $entity->setNumberOfEmployees(100);
                $entity->setIndustry('Internet');

                return;
        }

        parent::fillEntityData($key, $entity);
    }
}
