<?php

namespace OroCRM\Bundle\ContactBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\AccountBundle\Datagrid\AccountDatagridManager;

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
    protected function createQuery()
    {
        $query = parent::createQuery();
        $entityAlias = $query->getRootAlias();
        $query->andWhere(":contact MEMBER OF $entityAlias.contacts");

        return $query;
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
