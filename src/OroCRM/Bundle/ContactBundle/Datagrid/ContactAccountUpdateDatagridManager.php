<?php

namespace OroCRM\Bundle\ContactBundle\Datagrid;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory;

class ContactAccountUpdateDatagridManager extends ContactAccountDatagridManager
{
    /**
     * @var string
     */
    protected $hasContactExpression;

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldHasContact = new FieldDescription();
        $fieldHasContact->setName('has_contact');
        $fieldHasContact->setOptions(
            array(
                'type'            => FieldDescriptionInterface::TYPE_BOOLEAN,
                'label'           => $this->translate('Assigned'),
                'field_name'      => 'hasCurrentContact',
                'expression'      => $this->getHasContactExpression(),
                'nullable'        => false,
                'editable'        => true,
                'sortable'        => true,
                'filter_type'     => FilterInterface::TYPE_BOOLEAN,
                'filterable'      => true,
                'show_filter'     => true,
                'filter_by_where' => true,
            )
        );
        $fieldsCollection->add($fieldHasContact);

        parent::configureFields($fieldsCollection);
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareQuery(ProxyQueryInterface $query)
    {
        $this->applyJoinWithDefaultContact($query);

        $query->addSelect($this->getHasContactExpression() . ' AS hasCurrentContact', true);
    }

    /**
     * @return string
     */
    protected function getHasContactExpression()
    {
        if (null === $this->hasContactExpression) {
            /** @var EntityQueryFactory $queryFactory */
            $queryFactory = $this->queryFactory;
            $entityAlias = $queryFactory->getAlias();

            if ($this->getContact()->getId()) {
                $this->hasContactExpression =
                    "CASE WHEN " .
                    "(:contact MEMBER OF $entityAlias.contacts OR $entityAlias.id IN (:data_in)) AND " .
                    "$entityAlias.id NOT IN (:data_not_in) ".
                    "THEN true ELSE false END";
            } else {
                $this->hasContactExpression =
                    "CASE WHEN " .
                    "$entityAlias.id IN (:data_in) AND $entityAlias.id NOT IN (:data_not_in) ".
                    "THEN true ELSE false END";
            }
        }

        return $this->hasContactExpression;
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultSorters()
    {
        return array(
            'has_contact' => SorterInterface::DIRECTION_DESC,
            'name'        => SorterInterface::DIRECTION_ASC,
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getQueryParameters()
    {
        $additionalParameters = $this->parameters->get(ParametersInterface::ADDITIONAL_PARAMETERS);
        $contact = $this->getContact();

        // get list of included accounts
        $dataIn = array();
        if (!empty($additionalParameters['data_in'])) {
            $dataIn = $additionalParameters['data_in'];
        }
        if (!$contact->getId() && $contact->hasAccounts()) {
            // add predefined accounts
            foreach ($contact->getAccounts() as $account) {
                $accountId = $account->getId();
                if ($accountId) {
                    $dataIn[] = $accountId;
                }
            }
        }
        if (empty($dataIn)) {
            $dataIn[] = 0;
        }

        // get list of excluded accounts
        $dataNotIn = array(0);
        if (!empty($additionalParameters['data_not_in'])) {
            $dataNotIn = $additionalParameters['data_not_in'];
        }

        $parameters = array('data_in' => $dataIn, 'data_not_in' => $dataNotIn);

        if ($this->getContact()->getId()) {
            $parameters = array_merge(parent::getQueryParameters(), $parameters);
        }

        return $parameters;
    }
}
