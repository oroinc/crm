<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Oro\Bundle\GridBundle\Builder\DatagridBuilderInterface;
use Oro\Bundle\GridBundle\Builder\ListBuilderInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;

abstract class DatagridManager implements DatagridManagerInterface
{
    /**
     * @var DatagridBuilderInterface
     */
    protected $datagridBuilder;

    /**
     * @var ListBuilderInterface
     */
    protected $listBuilder;

    /**
     * @var QueryFactoryInterface
     */
    protected $queryFactory;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var ParametersInterface
     */
    protected $parameters;

    /**
     * @param DatagridBuilderInterface $datagridBuilder
     * @return void
     */
    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder)
    {
        $this->datagridBuilder = $datagridBuilder;
    }

    /**
     * @param ListBuilderInterface $listBuilder
     * @return void
     */
    public function setListBuilder(ListBuilderInterface $listBuilder)
    {
        $this->listBuilder = $listBuilder;
    }

    /**
     * @param QueryFactoryInterface $queryManager
     * @return void
     */
    public function setQueryFactory(QueryFactoryInterface $queryManager)
    {
        $this->queryFactory = $queryManager;
    }

    /**
     * @param TranslatorInterface $translator
     * @return void
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    /**
     * @param ValidatorInterface $validator
     * @return void
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param ParametersInterface $parameters
     */
    public function setParameters(ParametersInterface $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return DatagridInterface
     */
    public function getDatagrid()
    {
        // add datagrid fields
        $listCollection = $this->listBuilder->getBaseList();
        /** @var $fieldDescription FieldDescriptionInterface */
        foreach ($this->getListFields() as $fieldDescription) {
            $listCollection->add($fieldDescription);
        }

        // create datagrid
        $datagrid = $this->datagridBuilder->getBaseDatagrid(
            $this->queryFactory->createQuery(),
            $listCollection,
            $this->parameters->getParameters()
        );

        // add datagrid filters
        /** @var $fieldDescription FieldDescriptionInterface */
        foreach ($this->getFilters() as $fieldDescription) {
            $this->datagridBuilder->addFilter($datagrid, $fieldDescription);
        }

        // add datagrid sorters
        /** @var $fieldDescription FieldDescriptionInterface */
        foreach ($this->getSorters() as $fieldDescription) {
            // TODO: add sorter to datagrid
        }

        return $datagrid;
    }

    /**
     * Get list of datagrid fields
     *
     * @abstract
     * @return FieldDescriptionInterface[]
     */
    abstract protected function getListFields();

    /**
     * Get list of datagrid filters
     *
     * @return FieldDescriptionInterface[]
     */
    protected function getFilters()
    {
        return array();
    }

    /**
     * Get list of datagrid sorters
     *
     * @return array
     */
    protected function getSorters()
    {
        return array();
    }
}
