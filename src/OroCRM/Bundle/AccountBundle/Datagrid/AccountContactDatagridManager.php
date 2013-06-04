<?php

namespace OroCRM\Bundle\AccountBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class AccountContactDatagridManager extends FlexibleDatagridManager
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
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldId = new FieldDescription();
        $fieldId->setName('id');
        $fieldId->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_INTEGER,
                'label'       => 'ID',
                'field_name'  => 'id',
                'filter_type' => FilterInterface::TYPE_NUMBER,
                'show_column' => false
            )
        );
        $fieldsCollection->add($fieldId);

        $flexibleFieldOptions = array('show_filter' => true);
        $this->configureFlexibleField($fieldsCollection, 'first_name', $flexibleFieldOptions);
        $this->configureFlexibleField($fieldsCollection, 'last_name', $flexibleFieldOptions);
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
}
