<?php

namespace OroCRM\Bundle\ReportBundle\Entity\Manager;

use Doctrine\ORM\EntityManager;

class ReportManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Get report types
     *
     * @return array
     *  key => report name
     *  value => report label
     */
    public function getReportTypeChoices()
    {
        $result = [];
        $types = $this->em->getRepository('OroCRM\Bundle\ReportBundle\Entity\ReportType')->findAll();
        foreach ($types as $type) {
            $result[$type->getName()] = $type->getLabel();
        }

        return $result;
    }
} 