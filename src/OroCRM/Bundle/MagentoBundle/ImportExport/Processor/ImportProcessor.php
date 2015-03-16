<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Processor;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Processor\ImportProcessor as BaseImportProcessor;

class ImportProcessor extends BaseImportProcessor implements StepExecutionAwareInterface
{
    /**
     * @var ContextRegistry
     */
    protected $contextRegistry;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @param ContextRegistry $contextRegistry
     */
    public function setContextRegistry(ContextRegistry $contextRegistry)
    {
        $this->contextRegistry = $contextRegistry;
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        if (!$this->contextRegistry) {
            throw new \InvalidArgumentException('ContextRegistry is missing');
        }

        $this->stepExecution = $stepExecution;

        if ($this->strategy instanceof StepExecutionAwareInterface) {
            $this->strategy->setStepExecution($stepExecution);
        }

        $this->setImportExportContext($this->contextRegistry->getByStepExecution($this->stepExecution));
    }

    /**
     * @return ExecutionContext
     */
    protected function getJobContext()
    {
        /** @var JobExecution $jobExecution */
        $jobExecution = $this->stepExecution->getJobExecution();

        return $jobExecution->getExecutionContext();
    }
}
