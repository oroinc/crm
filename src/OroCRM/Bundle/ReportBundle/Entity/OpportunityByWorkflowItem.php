<?php

namespace OroCRM\Bundle\ReportBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

/**
 * @ORM\Entity
 * @ORM\Table(name="orocrm_report_opportunity_workflow_item")
 * @ORM\HasLifecycleCallbacks()
 */
class OpportunityByWorkflowItem
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Opportunity
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\SalesBundle\Entity\Opportunity")
     * @ORM\JoinColumn(name="opportunity_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $opportunity;

    /**
     * @var WorkflowItem
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowItem")
     * @ORM\JoinColumn(name="workflow_item_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $workflowItem;
}
