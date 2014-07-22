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
        $addressRepo = $this->templateManager
            ->getEntityRepository('Oro\Bundle\AddressBundle\Entity\Address');
        $userRepo    = $this->templateManager
            ->getEntityRepository('Oro\Bundle\UserBundle\Entity\User');
        $accountRepo = $this->templateManager
            ->getEntityRepository('OroCRM\Bundle\AccountBundle\Entity\Account');
        $contactRepo = $this->templateManager
            ->getEntityRepository('OroCRM\Bundle\ContactBundle\Entity\Contact');

        switch ($key) {
            case 'Jerry Coleman':
                $entity
                    ->setName('Oro Inc. Lead Name')
                    ->setCompanyName('Oro Inc.')
                    ->setOwner($userRepo->getEntity('John Doo'))
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime())
                    ->setAccount($accountRepo->getEntity('Coleman'))
                    ->setContact($contactRepo->getEntity('Jerry Coleman'))
                    ->setAddress($addressRepo->getEntity('Jerry Coleman'))
                    ->setEmail('JerryAColeman@armyspy.com')
                    ->setNamePrefix('Mr.')
                    ->setFirstName('Jerry')
                    ->setLastName('Coleman')
                    ->setNameSuffix('Jr.')
                    ->setStatus(new LeadStatus('New'))
                    ->setJobTitle('Manager')
                    ->setPhoneNumber('585-255-1127')
                    ->setWebsite('http://orocrm.com')
                    ->setNumberOfEmployees(100)
                    ->setIndustry('Internet');
                return;
        }

        parent::fillEntityData($key, $entity);
    }
}
