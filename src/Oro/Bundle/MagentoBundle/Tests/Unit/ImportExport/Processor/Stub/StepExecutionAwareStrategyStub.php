<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Importexport\Processor\Stub;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;

class StepExecutionAwareStrategyStub implements StrategyInterface, StepExecutionAwareInterface
{
    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
    }
}
