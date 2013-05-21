<?php

namespace Oro\Bundle\DataAuditBundle\Datagrid;

use Doctrine\ORM\Query;

use Gedmo\Loggable\LoggableListener;

use Oro\Bundle\GridBundle\Datagrid\DatagridManager;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Property\TwigTemplateProperty;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;

use Oro\Bundle\DataAuditBundle\Datagrid\AuditDatagridManager;

class AuditHistoryDatagridManager extends AuditDatagridManager
{
    /**
     * @var ParametersInterface
     */
    protected $parameters;

    /**
     * {@inheritDoc}
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $fieldAuthor = new FieldDescription();
        $fieldAuthor->setName('author');
        $fieldAuthor->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_TEXT,
                'label'       => 'Author',
                'field_name'  => 'author',
                'expression'  => $this->authorExpression,
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => false,
            )
        );
        $fieldAuthor->setFieldName('author');
        $fieldsCollection->add($fieldAuthor);

        $fieldLogged = new FieldDescription();
        $fieldLogged->setName('logged');
        $fieldLogged->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATETIME,
                'label'       => 'Logged At',
                'field_name'  => 'loggedAt',
                'filter_type' => FilterInterface::TYPE_DATETIME,
                'required'    => false,
                'sortable'    => true,
                'filterable'  => true,
                'show_filter' => false,
            )
        );
        $fieldsCollection->add($fieldLogged);

        $fieldDataOld = new FieldDescription();
        $fieldDataOld->setName('old');
        $fieldDataOld->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => 'Old values',
                'field_name'  => 'data',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $templateDataOldProperty = new TwigTemplateProperty(
            $fieldDataOld,
            'OroDataAuditBundle:Datagrid:Property/old.html.twig'
        );
        $fieldDataOld->setProperty($templateDataOldProperty);
        $fieldsCollection->add($fieldDataOld);

        $fieldDataNew = new FieldDescription();
        $fieldDataNew->setName('new');
        $fieldDataNew->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_HTML,
                'label'       => 'New values',
                'field_name'  => 'data',
                'filter_type' => FilterInterface::TYPE_STRING,
                'required'    => false,
                'sortable'    => false,
                'filterable'  => false,
                'show_filter' => false,
            )
        );
        $templateDataNewProperty = new TwigTemplateProperty(
            $fieldDataNew,
            'OroDataAuditBundle:Datagrid:Property/new.html.twig'
        );
        $fieldDataNew->setProperty($templateDataNewProperty);
        $fieldsCollection->add($fieldDataNew);
    }

    /**
     * @return array
     */
    protected function getDefaultSorters()
    {
        return array(
            'logged' => SorterInterface::DIRECTION_DESC
        );
    }

    /**
     * {@inheritDoc}
     */
    public function setParameters(ParametersInterface $parameters)
    {
        $this->parameters = $parameters;

        $this->parameters->set(ParametersInterface::PAGER_PARAMETERS, 5);
    }

    /**
     * @return ProxyQueryInterface
     */
    protected function createQuery()
    {
        $query = parent::createQuery();

        /**
         * TODO
         *  add default filter by ENTITY
         *  change per page values
         *
         */


        return $query;
    }
}
