<?php

namespace OroCRM\Bundle\SalesBundle\Handler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\Form\EntityField\Handler\Processor\AbstractEntityApiHandler;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;

class OpportunityApiHandler extends AbstractEntityApiHandler
{
    const ENTITY_CLASS = 'OroCRM\Bundle\SalesBundle\Entity\Opportunity';

    /** @var EntityManager */
    protected $entityManager;

    /** @var ConfigManager */
    protected $configManager;

    /** @var PropertyAccess **/
    private $accessor;

    /** @var UnitOfWork **/
    private $unitOfWork;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, ConfigManager $configManager)
    {
        $this->entityManager = $entityManager;
        $this->unitOfWork = $entityManager->getUnitOfWork();
        $this->configManager = $configManager;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeProcess($entity)
    {
        $this->unitOfWork->computeChangeSets();
        $changeset = $this->unitOfWork->getEntityChangeSet($entity);

        if (isset($changeset['status']) && !isset($changeset['probability'])) {
            $status = $changeset['status'][1];
            $probabilities = $this->configManager->get(
                'oro_crm_sales.default_opportunity_probabilities'
            );

            if (isset($probabilities[$status->getId()])) {
                $this->accessor->setValue($entity, 'probability', $probabilities[$status->getId()]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterProcess($entity)
    {
        return [
            'fields' => [
                'probability' => $this->accessor->getValue($entity, 'probability')
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return self::ENTITY_CLASS;
    }
}
