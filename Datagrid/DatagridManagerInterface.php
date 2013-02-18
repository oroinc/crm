<?php

namespace Oro\Bundle\GridBundle\Datagrid;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\GridBundle\Builder\DatagridBuilderInterface;
use Oro\Bundle\GridBundle\Builder\ListBuilderInterface;
use Oro\Bundle\GridBundle\Datagrid\QueryFactoryInterface;

interface DatagridManagerInterface
{
    /**
     * @param DatagridBuilderInterface $datagridBuilder
     * @return void
     */
    public function setDatagridBuilder(DatagridBuilderInterface $datagridBuilder);

    /**
     * @param ListBuilderInterface $listBuilder
     * @return void
     */
    public function setListBuilder(ListBuilderInterface $listBuilder);

    /**
     * @return DatagridInterface
     */
    public function getDatagrid();

    /**
     * @param QueryFactoryInterface $queryManager
     * @return void
     */
    public function setQueryFactory(QueryFactoryInterface $queryManager);

    /**
     * @param TranslatorInterface $translator
     * @return void
     */
    public function setTranslator(TranslatorInterface $translator);

    /**
     * @param ValidatorInterface $validator
     * @return void
     */
    public function setValidator(ValidatorInterface $validator);

    /**
     * @param ParametersInterface $parameters
     * @return void
     */
    public function setParameters(ParametersInterface $parameters);
}
