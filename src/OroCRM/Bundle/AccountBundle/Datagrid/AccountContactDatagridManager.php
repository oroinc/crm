<?php

namespace OroCRM\Bundle\AccountBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Datagrid\ContactDatagridManager;

class AccountContactDatagridManager extends ContactDatagridManager
{
    /**
     * @var Account
     */
    protected $account;

    /**
     * @param Account $account
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
        $this->routeGenerator->setRouteParameters(array('id' => $account->getId()));
    }

    /**
     * @return Account
     * @throws \LogicException
     */
    public function getAccount()
    {
        if (!$this->account) {
            throw new \LogicException('Datagrid manager has no configured Account entity');
        }

        return $this->account;
    }

    /**
     * {@inheritDoc}
     */
    protected function createQuery()
    {
        /** @var $query QueryBuilder */
        $query = parent::createQuery();
        $query->andWhere(':account MEMBER OF c.accounts');

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    protected function getQueryParameters()
    {
        return array('account' => $this->getAccount());
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
