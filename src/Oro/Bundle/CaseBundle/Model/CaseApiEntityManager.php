<?php

namespace Oro\Bundle\CaseBundle\Model;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class CaseApiEntityManager extends ApiEntityManager
{
    /**
     * @var CaseEntityManager
     */
    protected $caseManager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param CaseEntityManager $caseManager
     */
    public function __construct($class, ObjectManager $om, CaseEntityManager $caseManager)
    {
        $this->caseManager = $caseManager;
        parent::__construct($class, $om);
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return $this->caseManager->createCase();
    }
}
