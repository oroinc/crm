<?php

namespace OroCRM\Bundle\ContactBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\AccountBundle\Datagrid\AccountDatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

class ContactAccountDatagridManager extends AccountDatagridManager
{
    /**
     * @var Contact
     */
    protected $contact;

    /**
     * @param Contact $contact
     */
    public function setContact(Contact $contact)
    {
        $this->contact = $contact;
        $this->routeGenerator->setRouteParameters(array('id' => $contact->getId()));
    }

    /**
     * @return Contact
     * @throws \LogicException
     */
    public function getContact()
    {
        if (!$this->contact) {
            throw new \LogicException('Datagrid manager has no configured Contact entity');
        }

        return $this->contact;
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $this->applyJoinWithDefaultContact($query);

        $entityAlias = $query->getRootAlias();
        /** @var QueryBuilder $query */
        $query->andWhere(":contact MEMBER OF $entityAlias.contacts");
    }

    /**
     * {@inheritDoc}
     */
    protected function getQueryParameters()
    {
        return array('contact' => $this->getContact());
    }

    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        return array();
    }
}
