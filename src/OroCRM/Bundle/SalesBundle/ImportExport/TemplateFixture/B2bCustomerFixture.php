<?php

namespace OroCRM\Bundle\SalesBundle\ImportExport\TemplateFixture;

use Oro\Bundle\ImportExportBundle\TemplateFixture\AbstractTemplateRepository;
use Oro\Bundle\ImportExportBundle\TemplateFixture\TemplateFixtureInterface;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomerEmail;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomerPhone;

class B2bCustomerFixture extends AbstractTemplateRepository implements TemplateFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return 'OroCRM\Bundle\SalesBundle\Entity\B2bCustomer';
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
        return new B2bCustomer();
    }

    /**
     * @param string      $key
     * @param B2bCustomer $entity
     */
    public function fillEntityData($key, $entity)
    {
        $addressRepo = $this->templateManager->getEntityRepository('Oro\Bundle\AddressBundle\Entity\Address');
        $userRepo    = $this->templateManager->getEntityRepository('Oro\Bundle\UserBundle\Entity\User');
        $contactRepo = $this->templateManager->getEntityRepository('OroCRM\Bundle\ContactBundle\Entity\Contact');
        $leadRepo    = $this->templateManager->getEntityRepository('OroCRM\Bundle\SalesBundle\Entity\Lead');
        $accountRepo = $this->templateManager->getEntityRepository('OroCRM\Bundle\AccountBundle\Entity\Account');
        $channelRepo = $this->templateManager->getEntityRepository('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $opportunityRepo = $this->templateManager->getEntityRepository('OroCRM\Bundle\SalesBundle\Entity\Opportunity');

        switch ($key) {
            case 'Jerry Coleman':
                $entity->setName('Jerry Coleman');
                $entity->addLead($leadRepo->getEntity('Jerry Coleman'));
                $entity->setContact($contactRepo->getEntity('Jerry Coleman'));
                $entity->setAccount($accountRepo->getEntity('Coleman'));
                $entity->setOwner($userRepo->getEntity('John Doo'));
                $entity->setBillingAddress($addressRepo->getEntity('Jerry Coleman'));
                $entity->setShippingAddress($addressRepo->getEntity('Jerry Coleman'));
                $entity->setDataChannel($channelRepo->getEntity('Sales channel|b2b'));
                $entity->setCreatedAt(new \DateTime());
                $entity->setUpdatedAt(new \DateTime());
                $entity->addOpportunity($opportunityRepo->getEntity('Jerry Coleman'));

                $phone = new B2bCustomerPhone('585-255-1127');
                $phone->setPrimary(true);
                $entity->addPhone($phone);

                $email = new B2bCustomerEmail('JerryAColeman@armyspy.com');
                $email->setPrimary(true);
                $entity->addEmail($email);
                return;
        }

        parent::fillEntityData($key, $entity);
    }
}
