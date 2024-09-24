<?php

namespace Oro\Bundle\AccountBundle\ImportExport\TemplateFixture;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;

class AccountFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    #[\Override]
    public function getEntityClass()
    {
        return 'Oro\Bundle\AccountBundle\Entity\Account';
    }

    #[\Override]
    public function getData()
    {
        return $this->getEntityData('Coleman');
    }

    #[\Override]
    protected function createEntity($key)
    {
        return new Account();
    }

    /**
     * @param string  $key
     * @param Account $entity
     */
    #[\Override]
    public function fillEntityData($key, $entity)
    {
        $userRepo = $this->templateManager
            ->getEntityRepository('Oro\Bundle\UserBundle\Entity\User');
        $contactRepo = $this->templateManager
            ->getEntityRepository('Oro\Bundle\ContactBundle\Entity\Contact');
        $organizationRepo = $this->templateManager
            ->getEntityRepository('Oro\Bundle\OrganizationBundle\Entity\Organization');

        switch ($key) {
            case 'Coleman':
                $entity
                    ->setId(1)
                    ->setName($key)
                    ->setOwner($userRepo->getEntity('John Doo'))
                    ->setOrganization($organizationRepo->getEntity('default'))
                    ->addContact($contactRepo->getEntity('Jerry Coleman'))
                    ->setDefaultContact($contactRepo->getEntity('Jerry Coleman'));
                return;
            case 'Smith':
                $entity
                    ->setId(2)
                    ->setName($key)
                    ->setOwner($userRepo->getEntity('John Doo'))
                    ->setOrganization($organizationRepo->getEntity('default'))
                    ->addContact($contactRepo->getEntity('John Smith'))
                    ->setDefaultContact($contactRepo->getEntity('John Smith'));
                return;
        }

        parent::fillEntityData($key, $entity);
    }
}
