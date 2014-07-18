<?php

namespace OroCRM\Bundle\SalesBundle\ImportExport\TemplateFixture;

use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\OpportunityStatus;

class OpportunityFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return 'OroCRM\Bundle\SalesBundle\Entity\Opportunity';
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
        return new Opportunity();
    }

    /**
     * @param string      $key
     * @param Opportunity $entity
     */
    public function fillEntityData($key, $entity)
    {
        $userRepo    = $this->templateManager
            ->getEntityRepository('Oro\Bundle\UserBundle\Entity\User');
        $accountRepo = $this->templateManager
            ->getEntityRepository('OroCRM\Bundle\AccountBundle\Entity\Account');
        $contactRepo = $this->templateManager
            ->getEntityRepository('OroCRM\Bundle\ContactBundle\Entity\Contact');
        $leadRepo    = $this->templateManager
            ->getEntityRepository('OroCRM\Bundle\SalesBundle\Entity\Lead');

        switch ($key) {
            case 'Jerry Coleman':
                $entity
                    ->setName('Oro Inc. Opportunity Name')
                    ->setAccount($accountRepo->getEntity('Coleman'))
                    ->setCreatedAt(new \DateTime())
                    ->setUpdatedAt(new \DateTime())
                    ->setOwner($userRepo->getEntity('John Doo'))
                    ->setBudgetAmount(1000000)
                    ->setContact($contactRepo->getEntity('Jerry Coleman'))
                    ->setLead($leadRepo->getEntity('Jerry Coleman'))
                    ->setStatus(new OpportunityStatus('In Progress'));
                return;
        }

        parent::fillEntityData($key, $entity);
    }
}
