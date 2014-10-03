<?php

namespace OroCRM\Bundle\AccountBundle\ImportExport\TemplateFixture;

use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use OroCRM\Bundle\AccountBundle\Entity\Account;

class AccountFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return 'OroCRM\Bundle\AccountBundle\Entity\Account';
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->getEntityData('Coleman');
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($key)
    {
        return new Account();
    }

    /**
     * @param string  $key
     * @param Account $entity
     */
    public function fillEntityData($key, $entity)
    {
        $userRepo = $this->templateManager
            ->getEntityRepository('Oro\Bundle\UserBundle\Entity\User');
        $contactRepo = $this->templateManager
            ->getEntityRepository('OroCRM\Bundle\ContactBundle\Entity\Contact');
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
