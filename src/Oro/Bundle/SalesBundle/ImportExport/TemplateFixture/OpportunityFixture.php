<?php

namespace Oro\Bundle\SalesBundle\ImportExport\TemplateFixture;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\CurrencyBundle\Entity\MultiCurrency;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

/**
 * Opportunity demo data
 */
class OpportunityFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    #[\Override]
    public function getEntityClass()
    {
        return 'Oro\Bundle\SalesBundle\Entity\Opportunity';
    }

    #[\Override]
    public function getData()
    {
        return $this->getEntityData('Jerry Coleman');
    }

    #[\Override]
    protected function createEntity($key)
    {
        return new Opportunity();
    }

    /**
     * @param string      $key
     * @param Opportunity $entity
     */
    #[\Override]
    public function fillEntityData($key, $entity)
    {
        $userRepo         = $this->templateManager->getEntityRepository('Oro\Bundle\UserBundle\Entity\User');
        $contactRepo      = $this->templateManager->getEntityRepository('Oro\Bundle\ContactBundle\Entity\Contact');
        $leadRepo         = $this->templateManager->getEntityRepository('Oro\Bundle\SalesBundle\Entity\Lead');
        $organizationRepo = $this->templateManager
            ->getEntityRepository('Oro\Bundle\OrganizationBundle\Entity\Organization');

        switch ($key) {
            case 'Jerry Coleman':
                $entity->setName('Oro Inc. Opportunity Name');
                $entity->setCreatedAt(new \DateTime());
                $entity->setUpdatedAt(new \DateTime());
                $entity->setOwner($userRepo->getEntity('John Doo'));
                $entity->setOrganization($organizationRepo->getEntity('default'));
                $budgetAmount = MultiCurrency::create(100000, 'USD');
                $entity->setBudgetAmount($budgetAmount);
                $entity->setContact($contactRepo->getEntity('Jerry Coleman'));
                $entity->setLead($leadRepo->getEntity('Jerry Coleman'));

                $customer = new Customer();
                $customer->setTarget((new Account())->setName('Jerry Coleman'));

                $entity->setCustomerAssociation($customer);
                $statusName = 'in_progress';
                $internalId = ExtendHelper::buildEnumInternalId($statusName);
                $entity->setStatus(new EnumOption(Opportunity::INTERNAL_STATUS_CODE, $statusName, $internalId));

                return;
        }

        parent::fillEntityData($key, $entity);
    }
}
