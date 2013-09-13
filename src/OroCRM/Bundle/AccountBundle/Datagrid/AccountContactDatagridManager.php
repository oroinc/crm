<?php

namespace OroCRM\Bundle\AccountBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Datagrid\ContactDatagridManager;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

class AccountContactDatagridManager extends ContactDatagridManager
{
    /**
     * @var Account
     */
    protected $account;

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        parent::configureFields($fieldsCollection);
        $this->updateFieldsConfiguration($fieldsCollection);
    }

    /**
     * @param FieldDescriptionCollection $fieldsCollection
     */
    protected function updateFieldsConfiguration(FieldDescriptionCollection $fieldsCollection)
    {
        // disable all filters
        /** @var $fieldDescription FieldDescriptionInterface */
        foreach ($fieldsCollection as $fieldDescription) {
            $fieldDescription->setOption('filterable', false);
            $fieldDescription->setOption('show_filter', false);
        }
    }

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
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $this->applyJoinWithAddressAndCountry($query);

        $entityAlias = $query->getRootAlias();
        /** @var QueryBuilder $query */
        $query->andWhere(":account MEMBER OF $entityAlias.accounts");
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

    /**
     * {@inheritDoc}
     */
    protected function getMassActions()
    {
        return array();
    }
}
